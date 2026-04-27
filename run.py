from app import create_app
from extensions import db
from models import User

app = create_app('development')

if __name__ == '__main__':
    with app.app_context():
        db.create_all()
        print("Database tables created successfully!")
    print("Starting StitchHub at http://localhost:5000")
    app.run(debug=True, host='0.0.0.0', port=5000)