<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Load PHPMailer Files Manually
// This assumes your files are inside a folder named 'PHPMailer'
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// 2. Prevent "Cannot Redeclare" Crash
// This check stops the system from trying to create the function twice
if (!function_exists('sendResolvedNotification')) {

    function sendResolvedNotification($userEmail, $userName, $complaintId) {
        $mail = new PHPMailer(true);

        try {
            // --- SERVER SETTINGS ---
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            
            // ⚠️ ENTER YOUR GOOGLE APP PASSWORD BELOW ⚠️
            $mail->Username   = 'YOUR_GMAIL@gmail.com'; 
            $mail->Password   = 'YOUR_16_DIGIT_APP_PASSWORD'; 
            
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // --- RECIPIENTS ---
            $mail->setFrom('admin@disasterhelp.com', 'Disaster Help System');
            $mail->addAddress($userEmail, $userName);

            // --- CONTENT ---
            $mail->isHTML(true);
            $mail->Subject = "Update: Complaint #$complaintId Resolved";
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 8px;'>
                    <h2 style='color: #198754;'>Issue Resolved</h2>
                    <p>Dear <strong>$userName</strong>,</p>
                    <p>We are pleased to inform you that your complaint (ID: <strong>$complaintId</strong>) has been marked as <span style='color:green; font-weight:bold;'>Resolved</span>.</p>
                    <p>Thank you for helping us improve the community.</p>
                    <br>
                    <p>Regards,<br><strong>Civic Help Admin</strong></p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Uncomment the line below to debug if emails aren't sending
            // error_log("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>