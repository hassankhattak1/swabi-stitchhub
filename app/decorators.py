from functools import wraps
from flask import redirect, url_for, flash
from flask_login import current_user

def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated or current_user.role != 'admin':
            flash('Access denied. Admin privileges required.', 'danger')
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated_function

def tailor_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated or current_user.role != 'tailor':
            flash('Access denied. Tailor privileges required.', 'danger')
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated_function

def customer_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated or current_user.role != 'customer':
            flash('Access denied. Customer privileges required.', 'danger')
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated_function

def verified_tailor_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated:
            flash('Please login to continue.', 'danger')
            return redirect(url_for('auth.login'))
        if current_user.role != 'tailor':
            flash('Access denied. This section is for tailors only.', 'danger')
            return redirect(url_for('auth.login'))
        if current_user.tailor_profile and current_user.tailor_profile.verification_status != 'verified':
            flash('Your account is pending verification. Please wait for admin approval.', 'warning')
            return redirect(url_for('tailor.dashboard'))
        return f(*args, **kwargs)
    return decorated_function

def verified_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if not current_user.is_authenticated:
            flash('Please login to continue.', 'danger')
            return redirect(url_for('auth.login'))
        if not current_user.is_verified:
            flash('Your account is not verified. Please contact admin.', 'warning')
            return redirect(url_for('auth.login'))
        return f(*args, **kwargs)
    return decorated_function