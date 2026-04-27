from extensions import db, bcrypt
from flask_login import UserMixin
from datetime import datetime

class User(db.Model, UserMixin):
    __tablename__ = 'users'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    name = db.Column(db.String(100), nullable=False)
    email = db.Column(db.String(100), nullable=False, unique=True)
    password = db.Column(db.String(255), nullable=False)
    role = db.Column(db.String(20), nullable=False, default='customer')
    phone = db.Column(db.String(20))
    phone_verified = db.Column(db.Boolean, default=False)
    is_verified = db.Column(db.Boolean, default=False)
    address = db.Column(db.Text)
    bio = db.Column(db.Text)
    profile_pic = db.Column(db.String(255), default='default.png')
    is_active = db.Column(db.Boolean, default=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    orders_placed = db.relationship('Order', foreign_keys='Order.customer_id', lazy='dynamic')
    orders_received = db.relationship('Order', foreign_keys='Order.tailor_id', lazy='dynamic')
    measurements = db.relationship('Measurement', lazy='dynamic')
    tailor_profile = db.relationship('TailorProfile', backref='user', uselist=False)
    
    def set_password(self, password):
        self.password = bcrypt.generate_password_hash(password).decode('utf-8')
    
    def check_password(self, password):
        return bcrypt.check_password_hash(self.password, password)
    
    def is_admin(self):
        return self.role == 'admin'
    
    def is_tailor(self):
        return self.role == 'tailor'
    
    def is_customer(self):
        return self.role == 'customer'
    
    def get_rating(self):
        reviews = Review.query.filter_by(tailor_id=self.id).all()
        if not reviews:
            return 0
        return sum(r.rating for r in reviews) / len(reviews)

class OTPVerification(db.Model):
    __tablename__ = 'otp_verification'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    phone = db.Column(db.String(20), nullable=False)
    otp_code = db.Column(db.String(10), nullable=False)
    otp_type = db.Column(db.String(20), default='registration')
    expires_at = db.Column(db.DateTime, nullable=False)
    is_verified = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class PasswordReset(db.Model):
    __tablename__ = 'password_resets'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    token = db.Column(db.String(255), nullable=False)
    expires_at = db.Column(db.DateTime, nullable=False)
    is_used = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class TailorProfile(db.Model):
    __tablename__ = 'tailor_profiles'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    user_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False, unique=True)
    cnic = db.Column(db.String(20))
    experience = db.Column(db.String(50))
    location = db.Column(db.String(100))
    gender = db.Column(db.String(20), default='female')
    bio = db.Column(db.Text)
    verification_status = db.Column(db.String(20), default='pending')
    cnic_image = db.Column(db.String(255))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)

class Design(db.Model):
    __tablename__ = 'designs'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    tailor_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    title = db.Column(db.String(100), nullable=False)
    description = db.Column(db.Text)
    image_url = db.Column(db.String(255))
    price = db.Column(db.Float)
    category = db.Column(db.String(50))
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class Measurement(db.Model):
    __tablename__ = 'measurements'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    title = db.Column(db.String(100), nullable=False)
    chest = db.Column(db.Float)
    waist = db.Column(db.Float)
    hips = db.Column(db.Float)
    shoulder = db.Column(db.Float)
    sleeve_length = db.Column(db.Float)
    total_length = db.Column(db.Float)
    neck = db.Column(db.Float)
    arm_length = db.Column(db.Float)
    pant_length = db.Column(db.Float)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class Order(db.Model):
    __tablename__ = 'orders'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    tailor_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    design_id = db.Column(db.Integer, db.ForeignKey('designs.id', ondelete='SET NULL'))
    measurement_id = db.Column(db.Integer, db.ForeignKey('measurements.id', ondelete='SET NULL'))
    status = db.Column(db.String(20), default='pending')
    delivery_type = db.Column(db.String(20), default='pickup')
    delivery_address = db.Column(db.Text)
    total_amount = db.Column(db.Float, nullable=False)
    commission_amount = db.Column(db.Float, default=0)
    notes = db.Column(db.Text)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    updated_at = db.Column(db.DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    payments = db.relationship('Payment', backref='order', lazy='dynamic')
    
    @property
    def customer(self):
        return User.query.get(self.customer_id)
    
    @property
    def tailor(self):
        return User.query.get(self.tailor_id)
    
    @property
    def design(self):
        return Design.query.get(self.design_id) if self.design_id else None

class Payment(db.Model):
    __tablename__ = 'payments'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    order_id = db.Column(db.Integer, db.ForeignKey('orders.id', ondelete='CASCADE'), nullable=False)
    amount = db.Column(db.Float, nullable=False)
    status = db.Column(db.String(30), default='pending')
    transaction_id = db.Column(db.String(100))
    method = db.Column(db.String(50), default='Simulated Card')
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class Review(db.Model):
    __tablename__ = 'reviews'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    order_id = db.Column(db.Integer, db.ForeignKey('orders.id', ondelete='CASCADE'), nullable=False, unique=True)
    customer_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    tailor_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    rating = db.Column(db.Integer, nullable=False)
    comment = db.Column(db.Text)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)

class Message(db.Model):
    __tablename__ = 'messages'
    
    id = db.Column(db.Integer, primary_key=True, autoincrement=True)
    sender_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    receiver_id = db.Column(db.Integer, db.ForeignKey('users.id', ondelete='CASCADE'), nullable=False)
    message = db.Column(db.Text, nullable=False)
    is_read = db.Column(db.Boolean, default=False)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)