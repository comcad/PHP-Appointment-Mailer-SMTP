<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect to the form page if accessed directly
    header('Location: CHANGE THIS TO THE URL OF YOUR BOOKING PAGE');
    exit;
}
?>
<?php
  // 3 emails per hour limiter
    session_start();

// Initialize the email count if not set
if (!isset($_SESSION['email_count'])) {
    $_SESSION['email_count'] = 0;
    $_SESSION['first_email_time'] = time(); // Store the timestamp of the first email sent
}

// Check if the user has sent 3 emails within an hour
if ($_SESSION['email_count'] >= 3 && (time() - $_SESSION['first_email_time']) < 3600) {
    die("Error: You have reached the email limit (3 per hour). Please try again later.");
}

// If more than an hour has passed, reset the counter
if ((time() - $_SESSION['first_email_time']) >= 3600) {
    $_SESSION['email_count'] = 0;
    $_SESSION['first_email_time'] = time();
}

    if ($_SERVER['HTTP_REFERER'] !== 'CHANGE THIS TO THE URL OF YOUR BOOKING PAGE') {
    die('Unauthorized access.');
}


/**
 * Sends an email via SMTP using only PHP's built-in functions (for implicit TLS on port 465)
 * with certificate verification disabled.
 *
 * @param string $to      Recipient email address.
 * @param string $subject Email subject.
 * @param string $body    Email message body.
 *
 * @return true|string Returns true on success, or an error message on failure.
 */
function smtp_mail($to, $subject, $body) {
    // SMTP server configuration — update these with your details.
    $smtpServer = '';                   // Your SMTP server
    $port       = 465;                  // Port 465 for implicit TLS
    $username   = '';                   // Your SMTP username/email
    $password   = '';                   // Your SMTP password
    $from       = $username;            // Sender email

    // Create a stream context that disables certificate verification.
    // WARNING: Disabling certificate verification is insecure for production use.
    $contextOptions = [
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
            'allow_self_signed'=> true,
        ]
    ];
    $context = stream_context_create($contextOptions);

    // Open a socket connection using implicit TLS with the context.
    $socket = stream_socket_client("ssl://{$smtpServer}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
    if (!$socket) {
        return "Connection failed: $errstr ($errno)";
    }

    /**
     * Helper function to send a command to the SMTP server and check the response code.
     *
     * @param resource $socket       The open socket connection.
     * @param string   $command      The SMTP command to send.
     * @param string   $expectedCode The expected response code (e.g., "250").
     *
     * @return mixed False if the expected code wasn’t returned; otherwise, the server response.
     */
    function sendCommand($socket, $command, $expectedCode) {
        fwrite($socket, $command . "\r\n");
        $response = '';
        // Read all lines until the line's 4th character is a space (end of multi-line response)
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        if (substr($response, 0, 3) !== $expectedCode) {
            return false;
        }
        return $response;
    }

    // Read the initial server response.
    fgets($socket, 515);

    // 1. Send EHLO command.
    if (!sendCommand($socket, "EHLO localhost", "250")) {
        fclose($socket);
        return "EHLO command failed";
    }

    // (No STARTTLS available since this program only uses SSL on port 465)

    // 2. Authenticate using AUTH LOGIN.
    if (!sendCommand($socket, "AUTH LOGIN", "334")) {
        fclose($socket);
        return "AUTH LOGIN command failed";
    }
    if (!sendCommand($socket, base64_encode($username), "334")) {
        fclose($socket);
        return "Username not accepted";
    }
    if (!sendCommand($socket, base64_encode($password), "235")) {
        fclose($socket);
        return "Password not accepted";
    }

    // 3. Specify the sender and recipient.
    if (!sendCommand($socket, "MAIL FROM: <{$from}>", "250")) {
        fclose($socket);
        return "MAIL FROM command failed";
    }
    if (!sendCommand($socket, "RCPT TO: <{$to}>", "250")) {
        fclose($socket);
        return "RCPT TO command failed";
    }

    // 4. Send the DATA command.
    if (!sendCommand($socket, "DATA", "354")) {
        fclose($socket);
        return "DATA command failed";
    }

    // Prepare the email headers and content.
    $headers  = "From: <{$from}>\r\n";
    $headers .= "To: <{$to}>\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    $headers .= "\r\n";  // Blank line between headers and body

    // Send the email data, ending with a single period on a new line.
    fwrite($socket, $headers . $body . "\r\n.\r\n");
    $response = fgets($socket, 515);
    if (substr($response, 0, 3) !== "250") {
        fclose($socket);
        return "Error sending message: $response";
    }

    // 5. Close the SMTP session.
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    return true;
}

// Retrieve and sanitize form data
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
$date    = isset($_POST['date']) ? trim($_POST['date']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';



// Build the email content
$emailBody = "New Appointment Request:\n\n";
$emailBody .= "Name: " . $name . "\n";
$emailBody .= "Email: " . $email . "\n";
$emailBody .= "Phone: " . $phone . "\n";
$emailBody .= "Preferred Date: " . $date . "\n";
$emailBody .= "Details:\n" . $message . "\n";

// Send the email
$result = smtp_mail(
    "EMAIL GOES HERE", // Your email to receive the form data
    "New Appointment Request", 
    $emailBody
);

if ($result === true) {
    $_SESSION['email_count']++; // Increment email count
    // After successfully sending the email
    ob_start();
echo "<script>
        alert('We have received your request.');
        window.location.href = 'https://DOMAIN OR REDIRECTION PAGE AFTER FROM IS COMPLETE';
      </script>";
exit();
} else {
    echo "Error: " . $result;
}
?>
