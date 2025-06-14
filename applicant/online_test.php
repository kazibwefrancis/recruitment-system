<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Check if applicant is level1 shortlisted
$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$applicant = $pdo->prepare("SELECT * FROM applicants WHERE email = ? AND level1_status = 'shortlisted' AND level2_status = 'pending'");
$applicant->execute([$email]);
$applicant = $applicant->fetch();

if (!$applicant) {
    die("Invalid access or you haven't been shortlisted for this stage.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Calculate test score (example with 5 questions)
    $score = 0;
    $answers = [
        'q1' => 'a',
        'q2' => 'b',
        'q3' => 'c',
        'q4' => 'a',
        'q5' => 'd'
    ];
    
    foreach ($answers as $q => $correct) {
        if (isset($_POST[$q]) && $_POST[$q] === $correct) {
            $score += 20; // Each question worth 20 points
        }
    }
    
    // Update database
    $update = $pdo->prepare("UPDATE applicants SET test_score = ?, level2_status = ? WHERE id = ?");
    $status = $score >= 70 ? 'shortlisted' : 'rejected';
    $update->execute([$score, $status, $applicant['id']]);
    
    header("Location: application_status.php?email=" . urlencode($email));
    exit;
}
?>

<div class="online-test">
    <h2>Online Assessment</h2>
    <form method="POST">
        <div class="question">
            <p>1. What is PHP primarily used for?</p>
            <label><input type="radio" name="q1" value="a"> Web development</label>
            <label><input type="radio" name="q1" value="b"> Mobile apps</label>
            <label><input type="radio" name="q1" value="c"> Desktop software</label>
        </div>
        
        <!-- Add more questions similarly -->
        
        <button type="submit">Submit Test</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>