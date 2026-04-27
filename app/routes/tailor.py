from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from extensions import db
from models import User, TailorProfile, Design, Order, Payment
from app.decorators import tailor_required, verified_tailor_required

tailor_bp = Blueprint('tailor', __name__, url_prefix='/tailor')

@tailor_bp.route('/dashboard')
@login_required
@tailor_required
def dashboard():
    profile = TailorProfile.query.filter_by(user_id=current_user.id).first()
    if not profile:
        return redirect(url_for('tailor.complete_profile'))
    
    if profile.verification_status == 'pending':
        flash('Your account is pending verification. Please wait for admin approval.', 'warning')
    elif profile.verification_status == 'rejected':
        flash('Your account has been rejected. Please contact admin.', 'danger')
    
    orders = Order.query.filter_by(tailor_id=current_user.id).order_by(Order.created_at.desc()).all()
    total_earnings = sum(order.total_amount - order.commission_amount for order in orders if order.status in ['completed', 'delivered'])
    pending_orders = sum(1 for order in orders if order.status == 'pending')
    
    return render_template('tailor/dashboard.html', orders=orders, total_earnings=total_earnings, pending_orders=pending_orders, profile=profile)

@tailor_bp.route('/complete-profile', methods=['GET', 'POST'])
@login_required
@tailor_required
def complete_profile():
    existing = TailorProfile.query.filter_by(user_id=current_user.id).first()
    if existing:
        return redirect(url_for('tailor.dashboard'))
    
    if request.method == 'POST':
        profile = TailorProfile(
            user_id=current_user.id,
            cnic=request.form.get('cnic'),
            experience=request.form.get('experience'),
            location=request.form.get('location'),
            bio=request.form.get('bio'),
            verification_status='pending'
        )
        db.session.add(profile)
        db.session.commit()
        flash('Profile submitted for verification.', 'success')
        return redirect(url_for('tailor.dashboard'))
    
    return render_template('tailor/complete_profile.html')

@tailor_bp.route('/designs')
@login_required
@verified_tailor_required
def designs():
    designs = Design.query.filter_by(tailor_id=current_user.id).all()
    return render_template('tailor/designs.html', designs=designs)

@tailor_bp.route('/designs/add', methods=['GET', 'POST'])
@login_required
@verified_tailor_required
def add_design():
    if request.method == 'POST':
        design = Design(
            tailor_id=current_user.id,
            title=request.form.get('title'),
            description=request.form.get('description'),
            image_url=request.form.get('image_url'),
            price=request.form.get('price'),
            category=request.form.get('category')
        )
        db.session.add(design)
        db.session.commit()
        flash('Design added successfully!', 'success')
        return redirect(url_for('tailor.designs'))
    
    return render_template('tailor/add_design.html')

@tailor_bp.route('/designs/edit/<int:id>', methods=['GET', 'POST'])
@login_required
@verified_tailor_required
def edit_design(id):
    design = Design.query.get_or_404(id)
    if design.tailor_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('tailor.designs'))
    
    if request.method == 'POST':
        design.title = request.form.get('title')
        design.description = request.form.get('description')
        design.image_url = request.form.get('image_url')
        design.price = request.form.get('price')
        design.category = request.form.get('category')
        db.session.commit()
        flash('Design updated!', 'success')
        return redirect(url_for('tailor.designs'))
    
    return render_template('tailor/edit_design.html', design=design)

@tailor_bp.route('/designs/delete/<int:id>')
@login_required
@verified_tailor_required
def delete_design(id):
    design = Design.query.get_or_404(id)
    if design.tailor_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('tailor.designs'))
    
    db.session.delete(design)
    db.session.commit()
    flash('Design deleted.', 'success')
    return redirect(url_for('tailor.designs'))

@tailor_bp.route('/orders')
@login_required
@tailor_required
def orders():
    status_filter = request.args.get('status')
    query = Order.query.filter_by(tailor_id=current_user.id)
    
    if status_filter:
        query = query.filter_by(status=status_filter)
    
    orders = query.order_by(Order.created_at.desc()).all()
    return render_template('tailor/orders.html', orders=orders)

@tailor_bp.route('/order/<int:order_id>/accept')
@login_required
@verified_tailor_required
def accept_order(order_id):
    order = Order.query.get_or_404(order_id)
    if order.tailor_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('tailor.orders'))
    
    if order.status != 'pending':
        flash('Order cannot be accepted.', 'danger')
        return redirect(url_for('tailor.orders'))
    
    commission_rate = 0.10
    order.commission_amount = order.total_amount * commission_rate
    order.status = 'accepted'
    db.session.commit()
    flash('Order accepted!', 'success')
    return redirect(url_for('tailor.orders'))

@tailor_bp.route('/order/<int:order_id>/reject')
@login_required
@verified_tailor_required
def reject_order(order_id):
    order = Order.query.get_or_404(order_id)
    if order.tailor_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('tailor.orders'))
    
    if order.status != 'pending':
        flash('Order cannot be rejected.', 'danger')
        return redirect(url_for('tailor.orders'))
    
    order.status = 'rejected'
    db.session.commit()
    flash('Order rejected.', 'success')
    return redirect(url_for('tailor.orders'))

@tailor_bp.route('/order/<int:order_id>/status', methods=['POST'])
@login_required
@verified_tailor_required
def update_status(order_id):
    order = Order.query.get_or_404(order_id)
    if order.tailor_id != current_user.id:
        flash('Unauthorized.', 'danger')
        return redirect(url_for('tailor.orders'))
    
    new_status = request.form.get('status')
    valid_transitions = {
        'accepted': ['stitching'],
        'stitching': ['completed'],
        'completed': ['delivered']
    }
    
    if new_status not in valid_transitions.get(order.status, []):
        flash('Invalid status transition.', 'danger')
        return redirect(url_for('tailor.orders'))
    
    order.status = new_status
    db.session.commit()
    flash(f'Order status updated to {new_status}.', 'success')
    return redirect(url_for('tailor.orders'))

@tailor_bp.route('/profile')
@login_required
@tailor_required
def profile():
    profile = TailorProfile.query.filter_by(user_id=current_user.id).first()
    if not profile:
        return redirect(url_for('tailor.complete_profile'))
    return render_template('tailor/profile.html', profile=profile)

@tailor_bp.route('/profile/edit', methods=['GET', 'POST'])
@login_required
@tailor_required
def edit_profile():
    profile = TailorProfile.query.filter_by(user_id=current_user.id).first()
    if not profile:
        return redirect(url_for('tailor.complete_profile'))
    
    if request.method == 'POST':
        profile.experience = request.form.get('experience')
        profile.location = request.form.get('location')
        profile.bio = request.form.get('bio')
        db.session.commit()
        flash('Profile updated!', 'success')
        return redirect(url_for('tailor.profile'))
    
    return render_template('tailor/edit_profile.html', profile=profile)

@tailor_bp.route('/earnings')
@login_required
@verified_tailor_required
def earnings():
    orders = Order.query.filter_by(tailor_id=current_user.id).filter(
        Order.status.in_(['completed', 'delivered'])
    ).all()
    
    total_earnings = sum(order.total_amount - order.commission_amount for order in orders)
    pending_payments = sum(order.total_amount for order in orders if order.status == 'completed')
    
    payments = Payment.query.join(Order).filter(
        Order.tailor_id == current_user.id,
        Payment.status == 'transferred_to_tailor'
    ).all()
    
    return render_template('tailor/earnings.html', orders=orders, total_earnings=total_earnings, pending_payments=pending_payments, payments=payments)