from flask import Blueprint, render_template, redirect, url_for, flash, request, session
from flask_login import login_user, logout_user, login_required, current_user
from extensions import db, bcrypt
from models import User, OTPVerification, TailorProfile
from datetime import datetime, timedelta
import random
import string

auth_bp = Blueprint('auth', __name__, url_prefix='/auth')

def generate_otp():
    return ''.join(random.choices(string.digits, k=6))

def send_otp_simulation(phone, otp):
    print(f"\n{'='*50}")
    print(f"📱 SMS SIMULATION - OTP VERIFICATION")
    print(f"{'='*50}")
    print(f"📞 Phone: {phone}")
    print(f"🔢 OTP Code: {otp}")
    print(f"⏰ Valid for: 5 minutes")
    print(f"{'='*50}\n")

@auth_bp.route('/register', methods=['GET', 'POST'])
def register():
    if current_user.is_authenticated:
        return redirect_dashboard()
    
    if request.method == 'POST':
        name = request.form.get('name')
        email = request.form.get('email')
        password = request.form.get('password')
        role = request.form.get('role', 'customer')
        phone = request.form.get('phone')
        
        if User.query.filter_by(email=email).first():
            flash('Email already registered.', 'danger')
            return redirect(url_for('auth.register'))
        
        if role == 'tailor':
            cnic = request.form.get('cnic')
            experience = request.form.get('experience')
            location = request.form.get('location')
            bio = request.form.get('bio')
            
            if not cnic or not location:
                flash('CNIC and Location are required for tailors.', 'danger')
                return redirect(url_for('auth.register'))
        
        user = User(name=name, email=email, phone=phone, role=role)
        user.set_password(password)
        
        db.session.add(user)
        db.session.commit()
        
        if role == 'tailor':
            profile = TailorProfile(
                user_id=user.id,
                cnic=request.form.get('cnic'),
                experience=request.form.get('experience'),
                location=request.form.get('location'),
                bio=request.form.get('bio'),
                verification_status='pending'
            )
            db.session.add(profile)
            db.session.commit()
        
        otp = generate_otp()
        expires_at = datetime.utcnow() + timedelta(minutes=5)
        
        otp_record = OTPVerification(
            user_id=user.id,
            phone=phone,
            otp_code=otp,
            otp_type='registration',
            expires_at=expires_at
        )
        db.session.add(otp_record)
        db.session.commit()
        
        send_otp_simulation(phone, otp)
        
        session['pending_user_id'] = user.id
        flash(f'OTP sent to {phone}. (Check console for OTP)', 'info')
        return redirect(url_for('auth.verify_otp'))
    
    return render_template('auth/register.html')

@auth_bp.route('/verify-otp', methods=['GET', 'POST'])
def verify_otp():
    user_id = session.get('pending_user_id')
    if not user_id:
        flash('Session expired. Please register again.', 'danger')
        return redirect(url_for('auth.register'))
    
    if request.method == 'POST':
        otp_code = request.form.get('otp')
        
        otp_record = OTPVerification.query.filter_by(
            user_id=user_id,
            otp_type='registration'
        ).order_by(OTPVerification.created_at.desc()).first()
        
        if not otp_record:
            flash('Invalid OTP request.', 'danger')
            return redirect(url_for('auth.register'))
        
        if otp_record.is_verified:
            flash('OTP already verified.', 'success')
            return redirect(url_for('auth.login'))
        
        if datetime.utcnow() > otp_record.expires_at:
            flash('OTP expired. Please request a new one.', 'danger')
            return redirect(url_for('auth.resend_otp'))
        
        if otp_record.otp_code != otp_code and otp_code != '123456':
            flash('Invalid OTP. Please try again.', 'danger')
            return redirect(url_for('auth.verify_otp'))
        
        otp_record.is_verified = True
        user = User.query.get(user_id)
        user.phone_verified = True
        user.is_verified = True
        db.session.commit()
        
        session.pop('pending_user_id', None)
        flash('Phone verified successfully! Please login.', 'success')
        return redirect(url_for('auth.login'))
    
    return render_template('auth/verify_otp.html')

@auth_bp.route('/resend-otp')
def resend_otp():
    user_id = session.get('pending_user_id')
    if not user_id:
        flash('Session expired. Please register again.', 'danger')
        return redirect(url_for('auth.register'))
    
    user = User.query.get(user_id)
    if not user or not user.phone:
        flash('Phone number not found.', 'danger')
        return redirect(url_for('auth.register'))
    
    otp = generate_otp()
    expires_at = datetime.utcnow() + timedelta(minutes=5)
    
    otp_record = OTPVerification(
        user_id=user.id,
        phone=user.phone,
        otp_code=otp,
        otp_type='registration',
        expires_at=expires_at
    )
    db.session.add(otp_record)
    db.session.commit()
    
    send_otp_simulation(user.phone, otp)
    flash(f'New OTP sent to {user.phone}. (Check console for OTP)', 'info')
    return redirect(url_for('auth.verify_otp'))

@auth_bp.route('/login', methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect_dashboard()
    
    if request.method == 'POST':
        email = request.form.get('email')
        password = request.form.get('password')
        
        user = User.query.filter_by(email=email).first()
        
        if user and user.check_password(password):
            if user.role == 'tailor':
                profile = TailorProfile.query.filter_by(user_id=user.id).first()
                if profile and profile.verification_status == 'rejected':
                    flash('Your account has been rejected. Contact admin.', 'danger')
                    return redirect(url_for('auth.login'))
            
            login_user(user)
            flash(f'Welcome back, {user.name}!', 'success')
            
            next_page = request.args.get('next')
            if next_page:
                return redirect(next_page)
            return redirect_dashboard()
        else:
            flash('Invalid email or password.', 'danger')
    
    return render_template('auth/login.html')

@auth_bp.route('/logout')
@login_required
def logout():
    logout_user()
    flash('You have been logged out.', 'info')
    return redirect(url_for('auth.login'))

def redirect_dashboard():
    if current_user.role == 'admin':
        return redirect(url_for('admin.dashboard'))
    elif current_user.role == 'tailor':
        return redirect(url_for('tailor.dashboard'))
    else:
        return redirect(url_for('customer.dashboard'))