import os
from flask import Flask, render_template

def create_app(config_name='default'):
    from extensions import db, bcrypt, login_manager, csrf
    from models import User
    
    base_dir = os.path.abspath(os.path.dirname(__file__))
    template_dir = os.path.join(os.path.dirname(base_dir), 'templates')
    static_dir = os.path.join(os.path.dirname(base_dir), 'static')
    
    app = Flask(__name__, template_folder=template_dir, static_folder=static_dir)
    
    from config import config
    app.config.from_object(config[config_name])
    
    db.init_app(app)
    bcrypt.init_app(app)
    login_manager.init_app(app)
    csrf.init_app(app)
    
    login_manager.login_view = 'auth.login'
    login_manager.login_message_category = 'info'
    
    @login_manager.user_loader
    def load_user(user_id):
        return User.query.get(int(user_id))
    
    from app.routes.auth import auth_bp
    from app.routes.customer import customer_bp
    from app.routes.tailor import tailor_bp
    from app.routes.admin import admin_bp
    
    app.register_blueprint(auth_bp)
    app.register_blueprint(customer_bp)
    app.register_blueprint(tailor_bp)
    app.register_blueprint(admin_bp)
    
    @app.route('/')
    def index():
        from models import User, TailorProfile, Review
        verified_tailors = User.query.join(TailorProfile).filter(
            User.role == 'tailor',
            TailorProfile.verification_status == 'verified'
        ).limit(6).all()
        for tailor in verified_tailors:
            tailor.rating = tailor.get_rating()
            tailor.review_count = Review.query.filter_by(tailor_id=tailor.id).count()
        return render_template('index.html', tailors=verified_tailors)
    
    @app.errorhandler(404)
    def not_found(e):
        return render_template('404.html'), 404
    
    @app.errorhandler(500)
    def server_error(e):
        return render_template('500.html'), 500
    
    return app