<?php
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Get all pending applications
$stmt = $pdo->query("SELECT a.*, j.title as job_title, s.min_education, s.min_experience, s.required_skills 
                    FROM applicants a
                    LEFT JOIN jobs j ON a.job_id = j.id
                    LEFT JOIN shortlisting_criteria s ON a.job_id = s.job_id
                    WHERE a.level1_status = 'pending'");

while ($applicant = $stmt->fetch()) {
    $score = 0;
    
    // 1. Parse resume text (simplified example)
    $resume_text = extractTextFromFile($applicant['resume_path']);
    
    // 2. Check education (example)
    if ($applicant['min_education'] && strpos($resume_text, $applicant['min_education']) !== false) {
        $score += 30;
    }
    
    // 3. Check experience (example)
    if ($applicant['min_experience'] && hasSufficientExperience($resume_text, $applicant['min_experience'])) {
        $score += 30;
    }
    
    // 4. Check skills (example)
    $required_skills = explode(',', $applicant['required_skills']);
    $matched_skills = countMatchingSkills($resume_text, $required_skills);
    $score += ($matched_skills / count($required_skills)) * 40;
    
    // Update status based on score (threshold: 70)
    $status = $score >= 70 ? 'shortlisted' : 'rejected';
    
    $update = $pdo->prepare("UPDATE applicants SET level1_status = ? WHERE id = ?");
    $update->execute([$status, $applicant['id']]);
    
    // Send email notification
    sendApplicationStatusEmail($applicant['email'], $applicant['job_title'], $status);
}

function extractTextFromFile($file_path) {
    // Simplified - in reality you'd use a library for PDF/DOC parsing
    return file_get_contents($file_path);
}

function hasSufficientExperience($text, $years) {
    // Parse experience from text (simplified)
    return preg_match('/experience.*(\d+)\+? years/i', $text, $matches) 
           && intval($matches[1]) >= $years;
}

function countMatchingSkills($text, $skills) {
    $count = 0;
    foreach ($skills as $skill) {
        if (stripos($text, trim($skill)) !== false) {
            $count++;
        }
    }
    return $count;
}
?>