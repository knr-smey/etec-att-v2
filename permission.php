<?php
require_once(__DIR__ . '/config/db.php');

$requestId = trim($_GET['request_id'] ?? '');
$requestType = trim($_GET['request_type'] ?? '');
$studentName = trim($_GET['student_name'] ?? '');
$course = trim($_GET['course'] ?? '');
$period = trim($_GET['period'] ?? '');
$status = trim($_GET['status'] ?? '');

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getCurrentRequestStatus($conn, $requestId, $requestType)
{
    if ($requestId === '' || $requestType === '') {
        return null;
    }

    if ($requestType === 'absence') {
        $stmt = $conn->prepare("
            SELECT block_type, is_approved
            FROM student_attendance_block
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return null;
        }

        if (($row['block_type'] ?? '') === 'hard_lock') {
            return 'hard_lock';
        }

        return (int)($row['is_approved'] ?? 0) === 1 ? 'approved' : 'pending';
    }

    $stmt = $conn->prepare("
        SELECT status
        FROM student_permissions
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    return $row['status'] ?? null;
}

$dbStatus = getCurrentRequestStatus($conn, $requestId, $requestType);
$effectiveStatus = $dbStatus ?: $status;
$normalizedStatus = strtolower(trim((string)$effectiveStatus));
$isAlreadyApproved = in_array($normalizedStatus, ['approved', '1', '2', 'hard_lock', 'hard lock'], true);

$studentLabel = $studentName !== '' ? $studentName : '........................................';

$ruleItems = [
    'និស្សិតត្រូវគោរពវិន័យសាលា មករៀនឱ្យទៀងទាត់ និងគោរពពេលវេលា។',
    'និស្សិតត្រូវខិតខំរៀនសូត្រ ធ្វើកិច្ចការផ្ទះ និងបំពេញការងារដែលគ្រូបានដាក់ឱ្យបានគ្រប់គ្រាន់។',
    'និស្សិតមិនត្រូវអវត្តមានញឹកញាប់ ឬធ្វើអំពើដែលប៉ះពាល់ដល់ការសិក្សា និងកិត្តិយសសាលា។',
    'បើមិនគោរពតាមកិច្ចសន្យាខាងលើ និស្សិតត្រូវទទួលខុសត្រូវតាមវិន័យរបស់សាលា។',
];
?>
<!DOCTYPE html>
<html lang="km">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>កិច្ចសន្យានិស្សិត</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;500;600;700&display=swap');

    :root {
      --bg: #f7f1e7;
      --paper: #fffdfa;
      --ink: #1d1a16;
      --muted: #6d6458;
      --line: #dacdb8;
      --accent: #7c4c1f;
      --accent-dark: #5c3513;
      --success: #1f7a4f;
      --danger: #b13a2f;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      color: var(--ink);
      background:
        radial-gradient(circle at top, #fff9ef 0%, #f3eadb 46%, #eadfce 100%);
      font-family: "Kantumruy Pro", "Khmer OS Battambang", "Khmer OS Siemreap", "Noto Sans Khmer", sans-serif;
    }

    .wrap {
      max-width: 920px;
      margin: 0 auto;
      padding: 24px 16px 48px;
    }

    .paper {
      background: var(--paper);
      border: 1px solid var(--line);
      border-radius: 24px;
      box-shadow: 0 18px 44px rgba(73, 45, 17, 0.12);
      overflow: hidden;
    }

    .paper-head {
      padding: 26px 26px 18px;
      background:
        linear-gradient(135deg, rgba(124, 76, 31, 0.08), rgba(124, 76, 31, 0.02));
      border-bottom: 1px solid var(--line);
    }

    .ministry {
      margin: 0;
      text-align: center;
      font-size: 17px;
      letter-spacing: 0.02em;
    }

    .school {
      margin: 8px 0 0;
      text-align: center;
      font-size: 28px;
      font-weight: 700;
      color: var(--accent-dark);
    }

    .subtitle {
      margin: 8px 0 0;
      text-align: center;
      color: var(--muted);
      font-size: 15px;
      line-height: 1.7;
    }

    .paper-body {
      padding: 26px;
    }

    .meta-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 12px;
      margin-bottom: 24px;
    }

    .meta-box {
      border: 1px solid var(--line);
      border-radius: 16px;
      padding: 12px 14px;
      background: #fff8f0;
    }

    .meta-box span {
      display: block;
      margin-bottom: 4px;
      font-size: 12px;
      color: var(--muted);
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-family: Georgia, "Times New Roman", serif;
    }

    .contract-title {
      margin: 0 0 18px;
      text-align: center;
      font-size: 32px;
      color: var(--accent-dark);
    }

    .student-line {
      margin: 0 0 18px;
      font-size: 20px;
      line-height: 1.9;
    }

    .student-name {
      display: inline-block;
      min-width: 260px;
      /* border-bottom: 1px dotted var(--ink); */
      padding: 0 6px 2px;
      font-weight: 600;
    }

    .rule-box {
      border: 1px solid var(--line);
      border-radius: 20px;
      padding: 20px 20px 8px;
      background: #fffdf8;
    }

    .rule-box h2 {
      margin: 0 0 14px;
      font-size: 24px;
    }

    .rule-list {
      margin: 0;
      padding: 0 0 0 26px;
      line-height: 2;
      font-size: 20px;
    }

    .rule-list li {
      margin-bottom: 10px;
    }

    .write-box {
      margin-top: 24px;
      padding: 20px;
      border: 1px solid var(--line);
      border-radius: 20px;
      background: #fff9f2;
    }

    .write-box h3 {
      margin: 0 0 8px;
      font-size: 23px;
    }

    .write-box p {
      margin: 0 0 14px;
      color: var(--muted);
      line-height: 1.8;
      font-size: 15px;
    }

    .student-note {
      width: 100%;
      min-height: 170px;
      resize: vertical;
      border: 1px solid #cdbca4;
      border-radius: 16px;
      padding: 16px;
      font: inherit;
      font-size: 18px;
      line-height: 1.8;
      color: var(--ink);
      background: #fff;
      outline: none;
      transition: border-color .2s ease, box-shadow .2s ease;
    }

    .student-note:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 4px rgba(124, 76, 31, 0.12);
    }

    .student-note.error {
      border-color: var(--danger);
      box-shadow: 0 0 0 4px rgba(177, 58, 47, 0.08);
    }

    .signature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 14px;
      margin-top: 18px;
    }

    .field-line {
      padding-top: 6px;
      font-size: 17px;
      line-height: 1.8;
    }

    .field-line strong {
      font-weight: 700;
    }

    .line {
      display: inline-block;
      min-width: 150px;
      border-bottom: 1px dotted var(--ink);
      transform: translateY(-2px);
    }

    .action-row {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
      margin-top: 18px;
    }

    .submit-btn {
      border: 0;
      border-radius: 999px;
      background: var(--accent);
      color: #fff;
      padding: 13px 26px;
      font: inherit;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      transition: transform .15s ease, background-color .15s ease, opacity .15s ease;
    }

    .submit-btn:hover {
      transform: translateY(-1px);
      background: var(--accent-dark);
    }

    .submit-btn:disabled {
      opacity: .7;
      cursor: default;
      transform: none;
    }

    .helper-text {
      color: var(--muted);
      font-size: 14px;
      line-height: 1.7;
    }

    .message {
      display: none;
      margin-top: 16px;
      padding: 14px 16px;
      border-radius: 14px;
      font-size: 15px;
      line-height: 1.7;
    }

    .message.error {
      display: block;
      background: #fff1ef;
      color: var(--danger);
      border: 1px solid #f0c7c2;
    }

    .message.success {
      display: block;
      background: #eef9f2;
      color: var(--success);
      border: 1px solid #c6e7d2;
    }

    .foot-note {
      margin-top: 18px;
      text-align: center;
      color: var(--muted);
      font-size: 14px;
      line-height: 1.7;
    }

    @media (max-width: 640px) {
      .wrap {
        padding: 14px 10px 28px;
      }

      .paper-head,
      .paper-body {
        padding: 18px;
      }

      .contract-title {
        font-size: 26px;
      }

      .student-line,
      .rule-list {
        font-size: 18px;
      }

      .student-name {
        min-width: 180px;
      }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <div class="wrap">
    <div class="paper">
      <div class="paper-head">
        <p class="ministry">សំណុំបែបបទសម្រាប់និស្សិត</p>
        <h1 class="school">កិច្ចសន្យា / ពាក្យប្តេជ្ញា</h1>
        <p class="subtitle">
          សូមអានខ្លឹមសារខាងក្រោមឱ្យបានច្បាស់ បន្ទាប់មកសរសេរមតិ ឬពាក្យប្តេជ្ញារបស់អ្នកនៅក្នុងប្រអប់ខាងក្រោម។
        </p>
      </div>

      <div class="paper-body">
        <div class="meta-grid">
          <div class="meta-box">
            <span>Request ID</span>
            <?= h($requestId !== '' ? $requestId : '-') ?>
          </div>
          <div class="meta-box">
            <span>Student</span>
            <?= h($studentName !== '' ? $studentName : '-') ?>
          </div>
          <div class="meta-box">
            <span>Type</span>
            <?= h($requestType !== '' ? $requestType : '-') ?>
          </div>
          <div class="meta-box">
            <span>Course</span>
            <?= h($course !== '' ? $course : '-') ?>
          </div>
          <div class="meta-box">
            <span>Period</span>
            <?= h($period !== '' ? $period : '-') ?>
          </div>
          <div class="meta-box">
            <span>Status</span>
            <?= h($effectiveStatus !== '' ? $effectiveStatus : '-') ?>
          </div>
        </div>

        <h2 class="contract-title">លិខិតកិច្ចសន្យា</h2>
        <p class="student-line">
          ខ្ញុំបាទ/នាងខ្ញុំ៖<span class="student-name"><?= h($studentLabel) ?></span>
          <br>
          សូមធ្វើការប្តេជ្ញាចិត្តចំពោះសាលា ដោយអនុវត្តតាមខ្លឹមសារខាងក្រោម៖
        </p>

        <div class="rule-box">
          <h2>ខ្លឹមសារសន្យា</h2>
          <ol class="rule-list">
            <?php foreach ($ruleItems as $item): ?>
              <li><?= h($item) ?></li>
            <?php endforeach; ?>
          </ol>
        </div>

        <form id="studentCommitmentForm" class="write-box">
          <h3>សូមសរសេរពាក្យប្តេជ្ញា ឬ មូលហេតុរបស់អ្នក</h3>
          <p>
            អ្នកអាចសរសេរអំពីការយល់ព្រម ការសន្យាថានឹងកែប្រែ ឬមូលហេតុដែលចង់ជូនដំណឹងទៅសាលា។
          </p>

          <textarea
            id="studentNote"
            name="student_note"
            class="student-note"
            placeholder="សូមសរសេរពាក្យប្តេជ្ញា ឬមូលហេតុរបស់អ្នកនៅទីនេះ..."
          ></textarea>

          <div class="signature-grid">
            <div class="field-line"><strong>ឈ្មោះ:</strong> <span class="line"><?= h($studentName !== '' ? $studentName : '') ?></span></div>
            <div class="field-line"><strong>វគ្គសិក្សា:</strong> <span class="line"><?= h($course !== '' ? $course : '') ?></span></div>
            <div class="field-line"><strong>ប្រភេទសំណើ:</strong> <span class="line"><?= h($requestType !== '' ? $requestType : '') ?></span></div>
            <div class="field-line"><strong>ថ្ងៃខែឆ្នាំ:</strong> <span class="line" id="todayLine"></span></div>
          </div>

          <div class="action-row">
            <button type="submit" id="submitBtn" class="submit-btn" <?= $isAlreadyApproved ? 'disabled' : '' ?>>
              <?= $isAlreadyApproved ? 'Approved' : 'បញ្ជូន' ?>
            </button>
            <div class="helper-text">
              បន្ទាប់ពីបញ្ជូន របាយការណ៍នេះនឹងត្រូវរក្សាទុកលើឧបករណ៍នេះសម្រាប់ការត្រួតពិនិត្យបន្ទាប់។
            </div>
          </div>


        </form>

        <div class="foot-note">
          ទំព័រនេះត្រូវបានបង្កើតសម្រាប់ការបំពេញព័ត៌មានរបស់និស្សិតបន្ទាប់ពីស្កេន QR Code។
        </div>
      </div>
    </div>
  </div>

  <script>
    const form = document.getElementById('studentCommitmentForm');
    const noteInput = document.getElementById('studentNote');
    const submitBtn = document.getElementById('submitBtn');
    const formMessage = document.getElementById('formMessage');
    const todayLine = document.getElementById('todayLine');
    const storageKey = 'permission_note_' + <?= json_encode($requestId !== '' ? $requestId : 'guest') ?>;
    const requestId = <?= json_encode($requestId) ?>;
    const requestType = <?= json_encode($requestType) ?>;
    let isAlreadyApproved = <?= $isAlreadyApproved ? 'true' : 'false' ?>;

    function showMessage(type, text) {
      formMessage.className = 'message ' + type;
      formMessage.textContent = text;
    }

    function formatToday() {
      const now = new Date();
      const yyyy = now.getFullYear();
      const mm = String(now.getMonth() + 1).padStart(2, '0');
      const dd = String(now.getDate()).padStart(2, '0');
      return `${dd}/${mm}/${yyyy}`;
    }

    function loadSavedNote() {
      if (!window.localStorage) return;

      try {
        const raw = localStorage.getItem(storageKey);
        if (!raw) return;

        const saved = JSON.parse(raw);
        if (saved && saved.note) {
          noteInput.value = saved.note;
          showMessage('success', 'មានទិន្នន័យដែលបានបំពេញរួចនៅលើឧបករណ៍នេះ។ អ្នកអាចកែប្រែរួចបញ្ជូនម្តងទៀតបាន។');
        }
      } catch (error) {
      }
    }

    if (todayLine) {
      todayLine.textContent = formatToday();
    }

    loadSavedNote();

    if (isAlreadyApproved) {
      noteInput.setAttribute('disabled', 'disabled');
    }

    if (form) {
      form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (isAlreadyApproved) {
          showMessage('success', 'This request is already approved.');
          return;
        }

        const note = noteInput.value.trim();
        noteInput.classList.remove('error');

        if (!note) {
          noteInput.classList.add('error');
          showMessage('error', 'សូមបំពេញអត្ថបទក្នុងប្រអប់មុនពេលបញ្ជូន។');
          noteInput.focus();
          return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'កំពុងបញ្ជូន...';

        try {
          if (!requestId || !requestType) {
            throw new Error('Missing request information.');
          }

          const formData = new URLSearchParams();
          formData.append('id', requestId);
          formData.append('admin_comment', note);

          const response = await fetch('api.php?endpoint=approve_absence_block', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: formData.toString()
          });

          const result = await response.json();

          if (!response.ok || !result || !result.status) {
            throw new Error(result?.message || 'Submit failed');
          }

          if (window.localStorage) {
            localStorage.setItem(storageKey, JSON.stringify({
              note,
              requestId,
              requestType,
              studentName: <?= json_encode($studentName) ?>,
              course: <?= json_encode($course) ?>,
              savedAt: new Date().toISOString()
            }));
          }

          isAlreadyApproved = true;
          noteInput.setAttribute('disabled', 'disabled');
          submitBtn.disabled = true;
          submitBtn.textContent = 'Approved';

          if (window.history && window.history.replaceState) {
            const cleanUrl = new URL(window.location.href);
            cleanUrl.searchParams.set('status', 'APPROVED');
            window.history.replaceState({}, '', cleanUrl.toString());
          }

          showMessage('success', 'បានបញ្ជូនជោគជ័យ។');

          if (window.Swal) {
            Swal.fire({
              icon: 'success',
              title: 'ជោគជ័យ',
              text: 'បានបញ្ជូនជោគជ័យ។',
              confirmButtonText: 'បិទ'
            });
          }
        } catch (error) {
          showMessage('error', error.message || 'ការបញ្ជូនមិនទាន់ជោគជ័យទេ។ សូមព្យាយាមម្តងទៀត។');
        } finally {
          if (!isAlreadyApproved) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'បញ្ជូន';
          }
        }
      });
    }
  </script>
</body>
</html>
