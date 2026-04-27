from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from extensions import db
from models import User, TailorProfile, Design, Measurement, Order, Payment, Review
from app.decorators import customer_required, verified_required
import random
import string

customer_bp = Blueprint('customer', __name__, url_prefix='/customer')

@customer_bp.route('/dashboard')
@login_required
@customer_required
def dashboard():
    orders = Order.query.filter_by(customer_id=current_user.id).order_by(Order.created_at.desc()).all()
    return render_template('customer/dashboard.html', orders=orders)

@customer_bp.route('/tailors')
@login_required
@customer_required
def tailors():
    search = request.args.get('search', '')
    location = request.args.get('location', '')
    category = request.args.get('category', '')
    
    query = User.query.join(TailorProfile).filter(
        User.role == 'tailor',
        TailorProfile.verification_status == 'verified'
    )
    
    if search:
        query = query.filter(User.name.ilike(f'%{search}%'))
    if location:
        query = query.filter(TailorProfile.location.ilike(f'%{location}%'))
    
    tailors = query.all()
    
    for tailor in tailors:
        tailor.rating = tailor.get_rating()
        tailor.review_count = Review.query.filter_by(tailor_id=tailor.id).count()
    
    return render_template('customer/tailors.html', tailors=tailors)

@customer_bp.route('/tailor/<int:tailor_id>')
@login_required
@customer_required
def tailor_profile(tailor_id):
    tailor = User.query.get_or_404(tailor_id)
    if tailor.role != 'tailor':
        flash('Invalid tailor.', 'danger')
        return redirect(url_for('customer.tailors'))
    
    profile = TailorProfile.query.filter_by(user_id=tailor_id).first()
    if not profile or profile.verification_status != 'verified':
        flash('Tailor not found or not verified.', 'danger')
        return redirect(url_for('customer.tailors'))
    
    designs = Design.query.filter_by(tailor_id=tailor_id).all()
    reviews = Review.query.filter_by(tailor_id=tailor_id).order_by(Review.created_at.desc()).limit(10).all()
    rating = tailor.get_rating()
    
    return render_template('customer/tailor_profile.html', tailor=tailor, profile=profile, designs=designs, reviews=reviews, rating=rating)

@customer_bp.route('/measurements')
@login_required
@customer_required
def measurements():
    measurements = Measurement.query.filter_by(customer_id=current_user.id).all()
    return render_template('customer/measurements.html', measurements=measurements)

@customer_bp.route('/measurements/add', methods=['GET', 'POST'])
@login_required
@customer_required
def add_measurement():
    if request.method == 'POST':
        measurement = Measurement(
            customer_id=current_user.id,
            title=request.form.get('title'),
            chest=request.form.get('chest'),
            waist=request.form.get('waist'),
            hips=request.form.get('hips'),
            shoulder=request.form.get('shoulder'),
            sleeve_length=request.form.get('sleeve_length'),
            total_length=request.form.get('total_length'),
            neck=request.form.get('neck'),
            arm_length=request.form.get('arm_length'),
            pant_length=request.form.get('pant_length')
        )
        db.session.add(measurement)
        db.session.commit()
        flash('Measurement saved successfully!', 'success')
        return redirect(url_for('customer.measurements'))
    
    return render_template('customer/add_measurement.html')

@customer_bp.route('/measurements/delete/<int:id>')
@login_required
@customer_required
def delete_measurement(id):
    measurement = Measurement.query.get_or_404(id)
    if measurement.customer_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('customer.measurements'))
    
    db.session.delete(measurement)
    db.session.commit()
    flash('Measurement deleted.', 'success')
    return redirect(url_for('customer.measurements'))

@customer_bp.route('/order/<int:tailor_id>', methods=['GET', 'POST'])
@login_required
@customer_required
def place_order(tailor_id):
    tailor = User.query.get_or_404(tailor_id)
    if tailor.role != 'tailor':
        flash('Invalid tailor.', 'danger')
        return redirect(url_for('customer.tailors'))
    
    profile = TailorProfile.query.filter_by(user_id=tailor_id).first()
    if not profile or profile.verification_status != 'verified':
        flash('Tailor not available.', 'danger')
        return redirect(url_for('customer.tailors'))
    
    designs = Design.query.filter_by(tailor_id=tailor_id).all()
    measurements = Measurement.query.filter_by(customer_id=current_user.id).all()
    
    if request.method == 'POST':
        design_id = request.form.get('design_id')
        measurement_id = request.form.get('measurement_id')
        delivery_type = request.form.get('delivery_type')
        delivery_address = request.form.get('delivery_address')
        notes = request.form.get('notes')
        
        if design_id:
            design = Design.query.get(design_id)
            total_amount = design.price if design else 0
        else:
            total_amount = float(request.form.get('custom_price', 0))
        
        order = Order(
            customer_id=current_user.id,
            tailor_id=tailor_id,
            design_id=design_id if design_id else None,
            measurement_id=measurement_id if measurement_id else None,
            delivery_type=delivery_type,
            delivery_address=delivery_address if delivery_type == 'local' else None,
            total_amount=total_amount,
            notes=notes,
            status='pending'
        )
        db.session.add(order)
        db.session.commit()
        
        flash('Order placed successfully! Proceed to payment.', 'success')
        return redirect(url_for('customer.payment', order_id=order.id))
    
    return render_template('customer/place_order.html', tailor=tailor, designs=designs, measurements=measurements)

@customer_bp.route('/payment/<int:order_id>', methods=['GET', 'POST'])
@login_required
@customer_required
def payment(order_id):
    order = Order.query.get_or_404(order_id)
    if order.customer_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('customer.dashboard'))
    
    if order.payments.filter_by(status='paid_to_admin').first():
        flash('Order already paid.', 'info')
        return redirect(url_for('customer.dashboard'))
    
    if request.method == 'POST':
        transaction_id = ''.join(random.choices(string.ascii_uppercase + string.digits, k=12))
        
        payment = Payment(
            order_id=order.id,
            amount=order.total_amount,
            status='paid_to_admin',
            transaction_id=transaction_id
        )
        db.session.add(payment)
        db.session.commit()
        
        flash(f'Payment successful! Transaction ID: {transaction_id}', 'success')
        return redirect(url_for('customer.dashboard'))
    
    return render_template('customer/payment.html', order=order)

@customer_bp.route('/order/<int:order_id>/cancel')
@login_required
@customer_required
def cancel_order(order_id):
    order = Order.query.get_or_404(order_id)
    if order.customer_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('customer.dashboard'))
    
    if order.status not in ['pending', 'accepted']:
        flash('Cannot cancel order in current status.', 'danger')
        return redirect(url_for('customer.dashboard'))
    
    order.status = 'rejected'
    db.session.commit()
    flash('Order cancelled.', 'success')
    return redirect(url_for('customer.dashboard'))

@customer_bp.route('/order/<int:order_id>/review', methods=['GET', 'POST'])
@login_required
@customer_required
def review_order(order_id):
    order = Order.query.get_or_404(order_id)
    if order.customer_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('customer.dashboard'))
    
    if order.status != 'delivered':
        flash('Can only review delivered orders.', 'danger')
        return redirect(url_for('customer.dashboard'))
    
    existing_review = Review.query.filter_by(order_id=order.id).first()
    if existing_review:
        flash('Order already reviewed.', 'info')
        return redirect(url_for('customer.dashboard'))
    
    if request.method == 'POST':
        review = Review(
            order_id=order.id,
            customer_id=current_user.id,
            tailor_id=order.tailor_id,
            rating=request.form.get('rating'),
            comment=request.form.get('comment')
        )
        db.session.add(review)
        db.session.commit()
        flash('Review submitted!', 'success')
        return redirect(url_for('customer.dashboard'))
    
    return render_template('customer/review.html', order=order)