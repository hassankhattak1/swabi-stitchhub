from flask import Blueprint, render_template, redirect, url_for, flash, request
from flask_login import login_required, current_user
from extensions import db
from models import User, TailorProfile, Design, Order, Payment, Review
from app.decorators import admin_required
from sqlalchemy import func
import random
import string

admin_bp = Blueprint('admin', __name__, url_prefix='/admin')

COMMISSION_RATE = 0.10

@admin_bp.route('/dashboard')
@login_required
@admin_required
def dashboard():
    total_users = User.query.count()
    total_tailors = User.query.filter_by(role='tailor').count()
    verified_tailors = User.query.join(TailorProfile).filter(
        TailorProfile.verification_status == 'verified'
    ).count()
    pending_tailors = User.query.join(TailorProfile).filter(
        TailorProfile.verification_status == 'pending'
    ).count()
    
    total_orders = Order.query.count()
    pending_orders = Order.query.filter_by(status='pending').count()
    completed_orders = Order.query.filter(Order.status.in_(['completed', 'delivered'])).count()
    
    total_revenue = db.session.query(func.sum(Payment.amount)).filter(
        Payment.status == 'paid_to_admin'
    ).scalar() or 0
    
    recent_orders = Order.query.order_by(Order.created_at.desc()).limit(10).all()
    
    return render_template('admin/dashboard.html', 
                           total_users=total_users,
                           total_tailors=total_tailors,
                           verified_tailors=verified_tailors,
                           pending_tailors=pending_tailors,
                           total_orders=total_orders,
                           pending_orders=pending_orders,
                           completed_orders=completed_orders,
                           total_revenue=total_revenue,
                           recent_orders=recent_orders)

@admin_bp.route('/tailors')
@login_required
@admin_required
def tailors():
    status = request.args.get('status')
    query = User.query.join(TailorProfile).filter(User.role == 'tailor')
    
    if status:
        query = query.filter(TailorProfile.verification_status == status)
    
    tailors = query.all()
    return render_template('admin/tailors.html', tailors=tailors, status=status)

@admin_bp.route('/tailor/<int:user_id>/verify')
@login_required
@admin_required
def verify_tailor(user_id):
    user = User.query.get_or_404(user_id)
    if user.role != 'tailor':
        flash('User is not a tailor.', 'danger')
        return redirect(url_for('admin.tailors'))
    
    profile = TailorProfile.query.filter_by(user_id=user_id).first()
    if not profile:
        flash('Tailor profile not found.', 'danger')
        return redirect(url_for('admin.tailors'))
    
    profile.verification_status = 'verified'
    user.is_verified = True
    db.session.commit()
    flash(f'Tailor {user.name} has been verified.', 'success')
    return redirect(url_for('admin.tailors'))

@admin_bp.route('/tailor/<int:user_id>/reject')
@login_required
@admin_required
def reject_tailor(user_id):
    user = User.query.get_or_404(user_id)
    if user.role != 'tailor':
        flash('User is not a tailor.', 'danger')
        return redirect(url_for('admin.tailors'))
    
    profile = TailorProfile.query.filter_by(user_id=user_id).first()
    if not profile:
        flash('Tailor profile not found.', 'danger')
        return redirect(url_for('admin.tailors'))
    
    profile.verification_status = 'rejected'
    db.session.commit()
    flash(f'Tailor {user.name} has been rejected.', 'warning')
    return redirect(url_for('admin.tailors'))

@admin_bp.route('/customers')
@login_required
@admin_required
def customers():
    role_filter = request.args.get('role', 'customer')
    if role_filter == 'all':
        users = User.query.filter(User.role != 'admin').all()
    else:
        users = User.query.filter_by(role=role_filter).all()
    for user in users:
        user.order_count = Order.query.filter_by(customer_id=user.id).count()
    return render_template('admin/customers.html', users=users, role_filter=role_filter)

@admin_bp.route('/orders')
@login_required
@admin_required
def orders():
    status = request.args.get('status')
    query = Order.query
    
    if status:
        query = query.filter_by(status=status)
    
    orders = query.order_by(Order.created_at.desc()).all()
    return render_template('admin/orders.html', orders=orders, status=status)

@admin_bp.route('/order/<int:order_id>')
@login_required
@admin_required
def order_detail(order_id):
    order = Order.query.get_or_404(order_id)
    return render_template('admin/order_detail.html', order=order)

@admin_bp.route('/payments')
@login_required
@admin_required
def payments():
    status = request.args.get('status')
    query = Payment.query
    
    if status:
        query = query.filter_by(status=status)
    
    payments = query.order_by(Payment.created_at.desc()).all()
    return render_template('admin/payments.html', payments=payments, status=status)

@admin_bp.route('/payment/<int:payment_id>/transfer')
@login_required
@admin_required
def transfer_payment(payment_id):
    payment = Payment.query.get_or_404(payment_id)
    
    if payment.status != 'paid_to_admin':
        flash('Payment cannot be transferred.', 'danger')
        return redirect(url_for('admin.payments'))
    
    order = payment.order
    if order.status not in ['completed', 'delivered']:
        flash('Order must be completed before transfer.', 'danger')
        return redirect(url_for('admin.payments'))
    
    payment.status = 'transferred_to_tailor'
    db.session.commit()
    
    print(f"\n{'='*50}")
    print(f"💰 PAYMENT TRANSFER SIMULATION")
    print(f"{'='*50}")
    print(f"📋 Order ID: {order.id}")
    print(f"👤 Tailor: {order.tailor.name}")
    print(f"💵 Total Amount: PKR {payment.amount}")
    print(f"📊 Commission (10%): PKR {order.commission_amount}")
    print(f"💳 Transfer Amount: PKR {payment.amount - order.commission_amount}")
    print(f"🔖 Transaction ID: {payment.transaction_id}")
    print(f"{'='*50}\n")
    
    flash(f'Payment transferred to tailor. Transaction: {payment.transaction_id}', 'success')
    return redirect(url_for('admin.payments'))

@admin_bp.route('/reviews')
@login_required
@admin_required
def reviews():
    reviews = Review.query.order_by(Review.created_at.desc()).all()
    return render_template('admin/reviews.html', reviews=reviews)

@admin_bp.route('/reports')
@login_required
@admin_required
def reports():
    from datetime import datetime, timedelta
    
    period = request.args.get('period', 'month')
    
    if period == 'week':
        start_date = datetime.utcnow() - timedelta(days=7)
    elif period == 'year':
        start_date = datetime.utcnow() - timedelta(days=365)
    else:
        start_date = datetime.utcnow() - timedelta(days=30)
    
    orders = Order.query.filter(Order.created_at >= start_date).all()
    
    revenue = sum(p.amount for p in orders if p.payments.filter_by(status='paid_to_admin').first())
    completed = orders.filter_by(status='delivered').count()
    pending = orders.filter_by(status='pending').count()
    
    tailor_stats = []
    for user in User.query.filter_by(role='tailor').join(TailorProfile).filter(TailorProfile.verification_status == 'verified').all():
        tailor_orders = [o for o in orders if o.tailor_id == user.id]
        tailor_revenue = sum(o.total_amount - o.commission_amount for o in tailor_orders if o.status in ['completed', 'delivered'])
        tailor_stats.append({
            'tailor': user,
            'orders': len(tailor_orders),
            'revenue': tailor_revenue
        })
    
    tailor_stats.sort(key=lambda x: x['revenue'], reverse=True)
    
    return render_template('admin/reports.html', 
                           orders=orders, 
                           revenue=revenue, 
                           completed=completed, 
                           pending=pending,
                           tailor_stats=tailor_stats[:10],
                           period=period)

@admin_bp.route('/settings')
@login_required
@admin_required
def settings():
    return render_template('admin/settings.html')