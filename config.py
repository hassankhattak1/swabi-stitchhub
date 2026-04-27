import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'dev-secret-key-change-in-production'
    WTF_CSRF_ENABLED = False  # Disable CSRF for development
    
    # Database type - set to 'sqlite' for testing without MySQL
    DATABASE_TYPE = os.environ.get('DATABASE_TYPE', 'sqlite')
    
    if DATABASE_TYPE == 'sqlite':
        SQLALCHEMY_DATABASE_URI = 'sqlite:///stitchhub.db'
    else:
        # MySQL Database
        MYSQL_HOST = os.environ.get('MYSQL_HOST', 'localhost')
        MYSQL_PORT = os.environ.get('MYSQL_PORT', '3306')
        MYSQL_USER = os.environ.get('MYSQL_USER', 'root')
        MYSQL_PASSWORD = os.environ.get('MYSQL_PASSWORD', '')
        MYSQL_DATABASE = os.environ.get('MYSQL_DATABASE', 'stitchhub_db')
        
        # Handle empty password
        password_part = f":{MYSQL_PASSWORD}" if MYSQL_PASSWORD else ""
        SQLALCHEMY_DATABASE_URI = f"mysql+pymysql://{MYSQL_USER}{password_part}@{MYSQL_HOST}:{MYSQL_PORT}/{MYSQL_DATABASE}"
    
    SQLALCHEMY_TRACK_MODIFICATIONS = False
    
    # Session config
    PERMANENT_SESSION_LIFETIME = 86400  # 24 hours

class DevelopmentConfig(Config):
    DEBUG = True

class ProductionConfig(Config):
    DEBUG = False

config = {
    'development': DevelopmentConfig,
    'production': ProductionConfig,
    'default': DevelopmentConfig
}