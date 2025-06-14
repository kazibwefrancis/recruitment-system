<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/includes/header.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

echo "<pre>Checking database connection:\n";
if (!isset($pdo)) {
    die("ERROR: \$pdo variable not set in db_connect.php");
}

try {
    $pdo->query("SELECT 1");
    echo "Database connection successful!";
} catch (PDOException $e) {
    die("Connection test failed: " . $e->getMessage());
}
echo "</pre>";

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
$job = null;
if ($job_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND is_active = TRUE");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    $resume_path = '';
    if (isset($_FILES['resume'])) {
        $upload_dir = dirname(__DIR__) . '/assets/uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_name = uniqid() . '_' . basename($_FILES['resume']['name']);
        $target_file = $upload_dir . $file_name;
        $allowed_types = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $file_type = $_FILES['resume']['type'];
        if (!in_array($file_type, $allowed_types)) {
            die("Error: Only PDF and DOC/DOCX files are allowed.");
        }
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target_file)) {
            $resume_path = 'assets/uploads/' . $file_name;
        } else {
            die("Error uploading file. Please try again or contact support.");
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO applicants 
                              (job_id, first_name, last_name, email, phone, resume_path, cover_letter) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $job_id ?: NULL,
            $first_name,
            $last_name,
            $email,
            $phone,
            $resume_path,
            trim($_POST['cover_letter'])
        ]);
        $applicantId = $pdo->lastInsertId();

        // ---- EMAIL SENDING IMPLEMENTATION ----
        require_once dirname(__DIR__) . '/admin/Email.php';
        $emailSender = new EmailSender();
        $sent = $emailSender->sendCongratulatoryEmail($email, $first_name, $last_name);

        // Feedback to user
        echo '<div class="success-notice" style="background:#d4edda;color:#155724;padding:15px;margin:20px;border:1px solid #c3e6cb;border-radius:5px;">';
        echo '<h3>Application Submitted Successfully!</h3>';
        echo '<p>Reference ID: ' . htmlspecialchars($applicantId) . '</p>';
        if ($sent) {
            echo '<p>A confirmation email has been sent to your email address.</p>';
        } else {
            echo '<p><strong>Warning:</strong> The confirmation email could not be sent. Please check your email address.</p>';
        }
        echo '<p>You will be redirected in 5 seconds...</p>';
        echo '</div>';

        echo '<meta http-equiv="refresh" content="5;url=application_status.php?email=' . urlencode($email) . '">';
        flush();
        sleep(5);
        exit;
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicant Form - Recruitment System</title>
    <link rel="stylesheet" href="/recruitment-system/css/style.css">
</head>
<body>
<div class="application-form">
    <h2><?= $job ? "Apply for " . htmlspecialchars($job['title']) : "General Application" ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="job_id" value="<?= htmlspecialchars($job_id) ?>">
        
        <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
        </div>
        
        <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
        </div>
        
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" required>
        </div>
        
        <div class="form-group">
            <label>Resume (PDF/DOC)</label>
            <input type="file" name="resume" accept=".pdf,.doc,.docx" required>
            <small>Max file size: 2MB</small>
        </div>
        
        <div class="form-group">
            <label>Cover Letter</label>
            <textarea name="cover_letter" rows="5"></textarea>
        </div>
        
        <button type="submit">Submit Application</button>
    </form>
</div>

<script src="/recruitment-system/js/script.js"></script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
</body>
</html>