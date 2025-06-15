# **PHP Appointment Notification Mailer using SMTP**

This project is a simple booking system that uses PHP to handle form submissions and SMTP to send appointment notifications to the website owner.



![Screenshot_20250328_104129](https://github.com/user-attachments/assets/bbaaef82-d9cd-4480-831e-42c6631b51ed)


# Installation

Clone this repository:
```
git clone https://github.com/comcad/PHP-Appointment-Mailer-SMTP.git
```
Navigate to the project directory:
```
cd PHP-Appointment-Mailer-SMTP
```
Configure SMTP settings in .env:
```
SMTP_SERVER=mail.yourdomain.com

SMTP_PORT=465

SMTP_USERNAME=username@yourdomain.com

SMTP_PASSWORD=yourpassword

SMTP_FROM=username@yourdomain.com

SMTP_TO=username@yourdomain.com

FORM_URL=https://URL.com/bookings/

REDIRECT_TO=https://URL.com/
```

# Usage

Upload the files to your web server.

Ensure your server supports PHP and SMTP connections.

Access the booking form via your website.

Fill in the required information.

Submit the form to receive an email notification of the booking request.

Feel free to fork this repository and submit pull requests for any improvements.
