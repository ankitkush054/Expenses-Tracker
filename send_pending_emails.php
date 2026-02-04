<?php
include 'config.php';
include 'sendEmail.php'; // PHPMailer function

$sql = "SELECT * FROM email_queue WHERE status = 'pending'";
$result = $con->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $to = $row['recipient'];
        $subject = $row['subject'];
        $message = $row['message'];

        if (sendEmail($to, $subject, $message)) {
            $con->query("UPDATE email_queue SET status = 'sent' WHERE id = $id");
            echo "✅ Email sent to: $to<br>";
        } else {
            $con->query("UPDATE email_queue SET status = 'failed' WHERE id = $id");
            echo "❌ Failed to send email to: $to<br>";
        }
    }
} else {
    echo "No pending emails.<br>";
}
?>
