<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// The autoloader is in the root directory
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";

function send_gym_email($recipient_email, $recipient_name, $subject, $heading, $message_html) {
    global $connGym;
    
    // Default fallback colors
    $primary_color = '#000000';
    $accent_color = '#d4af37';
    
    // The script calling this function is running inside the specific gym's directory (e.g., Gyms/royal-gym/admin/requests.php)
    // So we can dynamically read the gym's specific base.css relative to that calling script!
    $css_path = '../css/base.css';
    if (defined('GYM_BASE_URL')) {
        $css_path = $_SERVER['DOCUMENT_ROOT'] . GYM_BASE_URL . '/css/base.css';
    }
    
    if (file_exists($css_path)) {
        $css_content = file_get_contents($css_path);
        // Extract --primary hex color dynamically
        if (preg_match('/--primary:\s*(#[a-fA-F0-9]+)/', $css_content, $matches)) {
            $primary_color = $matches[1];
        }
        // Extract --accent hex color dynamically
        if (preg_match('/--accent:\s*(#[a-fA-F0-9]+)/', $css_content, $matches)) {
            $accent_color = $matches[1];
        }
    }
    
    // Get the Gym's Name from the database
    $gym_name = "Our Gym";
    if (isset($connGym)) {
        $stmt = $connGym->prepare("SELECT name FROM gyminfo LIMIT 1");
        $stmt->execute();
        $info = $stmt->get_result()->fetch_assoc();
        if ($info && !empty($info['name'])) {
            $gym_name = $info['name'];
        }
        $stmt->close();
    }
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        
        $mail->Username = 'yacinetechcodes2@gmail.com';
        $mail->Password = 'xezarqomauxiizxg';
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        $mail->setFrom('yacinetechcodes2@gmail.com', $gym_name);
        $mail->addAddress($recipient_email, $recipient_name);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        // This is the universal HTML template. 
        // We inject the CSS colors directly into the style tags because email clients (like Gmail) strip external CSS links.
        $htmlTemplate = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
                .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
                .email-header { background-color: {$primary_color}; padding: 30px 20px; text-align: center; color: #ffffff; }
                .email-header h1 { margin: 0; font-size: 24px; color: #ffffff;}
                .email-body { padding: 30px; color: #333333; line-height: 1.6; }
                .email-body h2 { color: {$primary_color}; margin-top: 0; }
                .email-footer { background-color: #eeeeee; padding: 15px; text-align: center; font-size: 12px; color: #777777; border-top: 1px solid #dddddd; }
                .btn { display: inline-block; padding: 12px 24px; margin-top: 20px; background-color: {$accent_color}; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>" . htmlspecialchars($gym_name) . "</h1>
                </div>
                <div class='email-body'>
                    <h2>{$heading}</h2>
                    {$message_html}
                </div>
                <div class='email-footer'>
                    &copy; " . date('Y') . " " . htmlspecialchars($gym_name) . ". All rights reserved.
                </div>
            </div>
        </body>
        </html>
        ";
        
        $mail->Body = $htmlTemplate;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $message_html));
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function send_membership_pending_email($toEmail, $toName) {
    $subject = "Membership Request Received";
    $heading = "Welcome!";
    $message_html = "<p>Hello " . htmlspecialchars($toName) . ",</p>
                     <p>We have received your membership request and it is currently pending approval by the admin.</p>
                     <p>We will notify you once it has been approved.</p>";
    return send_gym_email($toEmail, $toName, $subject, $heading, $message_html);
}

function send_membership_approved_email($recipient_email, $recipient_name) {
    $subject = "Your Membership is Approved!";
    $heading = "Welcome to the Gym!";
    $message_html = "<p>Hello <strong>" . htmlspecialchars($recipient_name) . "</strong>,</p>";
    $message_html .= "<p>Great news! Your membership request has been <strong>approved</strong>.</p>";
    $message_html .= "<p>Your subscription is now active. You can log in using your default password: <strong>member123</strong>.</p>";
    $message_html .= "<p>We strongly recommend logging in and changing your password as soon as possible.</p>";
    $message_html .= "<a href='#' class='btn'>Log In Now</a>";
    return send_gym_email($recipient_email, $recipient_name, $subject, $heading, $message_html);
}

function send_request_rejected_email($recipient_email, $recipient_name, $request_type) {
    $subject = "Your Request has been Rejected";
    $heading = "Request Update";
    $message_html = "<p>Hello <strong>" . htmlspecialchars($recipient_name) . "</strong>,</p>";
    $message_html .= "<p>We are writing to inform you that your " . htmlspecialchars($request_type) . " request has been <strong>rejected</strong>.</p>";
    $message_html .= "<p>If you have any questions, please contact the gym administration.</p>";
    return send_gym_email($recipient_email, $recipient_name, $subject, $heading, $message_html);
}
?>
