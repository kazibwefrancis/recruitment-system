<?php
require_once '../includes/header.php';
require_once '../includes/db_connect.php';
require_admin();

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// Get applicants
$sql = "SELECT * FROM applicants" . ($job_id ? " WHERE job_id = ?" : "");
$stmt = $pdo->prepare($sql);
$stmt->execute($job_id ? [$job_id] : []);
$applicants = $stmt->fetchAll();
?>

<div class="applicants-list">
    <h2>Applicants Management</h2>
    
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Job</th>
                <th>Level 1</th>
                <th>Level 2</th>
                <th>Test Score</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applicants as $app): ?>
            <tr>
                <td><?= htmlspecialchars($app['first_name'] . ' ' . $app['last_name']) ?></td>
                <td><?= htmlspecialchars($app['email']) ?></td>
                <td>
                    <?php if ($app['job_id']): ?>
                        <?= getJobTitle($pdo, $app['job_id']) ?>
                    <?php else: ?>
                        General
                    <?php endif; ?>
                </td>
                <td class="status-<?= $app['level1_status'] ?>"><?= ucfirst($app['level1_status']) ?></td>
                <td class="status-<?= $app['level2_status'] ?>"><?= ucfirst($app['level2_status']) ?></td>
                <td><?= $app['test_score'] ? $app['test_score'] : 'N/A' ?></td>
                <td>
                    <a href="view_application.php?id=<?= $app['id'] ?>">View</a>
                    <a href="send_test_link.php?id=<?= $app['id'] ?>" <?= $app['level1_status'] != 'shortlisted' ? 'disabled' : '' ?>>Send Test</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>