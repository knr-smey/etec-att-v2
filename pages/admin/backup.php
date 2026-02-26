<?php
session_start();
require_once(__DIR__ . '/../../config/db.php');

// Check if user is admin
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: /index.php');
    exit;
}
?>

<div class="container-fluid p-0">
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm rounded-3 overflow-hidden border-0">
                <div class="card-body p-0">
                    <!-- Header -->
                    <div class="w-full py-4 bg-etec-color text-light text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-cloud-arrow-down me-2"></i>Database Backup
                        </h3>
                        <p class="mb-0 text-white-50">Backup your database and send it to Telegram</p>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <!-- Info Alert -->
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>How it works:</strong> We'll create a complete database backup as a <strong>.SQL file</strong>. You can download it or send directly to Telegram for cloud storage. The file contains all your database tables and data.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>

                        <!-- Backup Options -->
                        <div class="row g-3">
                            <!-- Download Option -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light p-3 text-center h-100">
                                    <i class="bi bi-download text-primary mb-2" style="font-size: 2rem;"></i>
                                    <h5 class="card-title">Download SQL Backup</h5>
                                    <p class="card-text text-muted small">Create and download your database backup to your device</p>
                                    <button class="btn btn-primary btn-sm" id="downloadBtn">
                                        <i class="bi bi-download me-1"></i> Download
                                    </button>
                                </div>
                            </div>

                            <!-- Send to Telegram Option -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light p-3 text-center h-100">
                                    <i class="bi bi-telegram text-info mb-2" style="font-size: 2rem;"></i>
                                    <h5 class="card-title">Send to Telegram</h5>
                                    <p class="card-text text-muted small">Send your database backup to Telegram for cloud storage</p>
                                    <button class="btn btn-info btn-sm" id="telegramBtn">
                                        <i class="bi bi-send me-1"></i> Send to Telegram
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Both Option -->
                        <div class="mt-3">
                            <button class="btn btn-success w-100" id="bothBtn">
                                <i class="bi bi-arrow-left-right me-1"></i> Download & Send to Telegram
                            </button>
                        </div>

                        <!-- Progress Section -->
                        <div id="progressSection" class="mt-4 d-none">
                            <div class="progress" style="height: 25px;">
                                <div id="progressBar" 
                                    class="progress-bar bg-etec-color progress-bar-striped progress-bar-animated" 
                                    style="width: 0%;">
                                </div>
                            </div>
                            <p id="progressText" class="text-center mt-2 text-muted small">Preparing backup...</p>
                        </div>

                        <!-- Status Alert -->
                        <div id="statusAlert" class="alert alert-dismissible fade mt-3 d-none shadow-sm border-0" role="alert"></div>

                    <hr class="my-4">
                    <h5 class="mb-3">Backup History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr class="table-light">
                                    <th>Date & Time</th>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="backupHistoryTable">
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-3">
                                        <i class="bi bi-inbox"></i> No backups yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Debug/Troubleshooting Section -->
                    <hr class="my-4">
                    <div class="accordion" id="troubleshootingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#troubleshootingCollapse">
                                    <i class="bi bi-tools me-2"></i> Troubleshooting & System Check
                                </button>
                            </h2>
                            <div id="troubleshootingCollapse" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                <div class="accordion-body">
                                    <p class="text-muted mb-3">Click the button below to check if your system is configured correctly for backups:</p>
                                    <button class="btn btn-sm btn-outline-secondary me-2" id="checkRequirementsBtn">
                                        <i class="bi bi-search me-1"></i> Check System Requirements
                                    </button>
                                    <button class="btn btn-sm btn-outline-info me-2" id="testApiBtn">
                                        <i class="bi bi-bug me-1"></i> Test API Connection
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" id="cleanupBtn">
                                        <i class="bi bi-trash me-1"></i> Clean Old Backups
                                    </button>
                                    <div id="requirementsResult" class="mt-3"></div>
                                    <div id="apiTestResult" class="mt-3"></div>
                                    <div id="cleanupResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Side Info -->
        <div class="col-md-4">
            <div class="card shadow-sm rounded-3 bg-light border-0 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-shield-check text-success me-2"></i>Backup Benefits
                    </h5>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Complete Database:</strong> Export all tables and data as SQL
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Data Protection:</strong> Protect against accidental deletion
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Security:</strong> Safe from hacking or server issues
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Quick Recovery:</strong> Restore your database anytime
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success me-2"></i>
                            <strong>Telegram Storage:</strong> Send to cloud for free backup
                        </li>
                    </ul>

                    <hr>

                    <h6 class="text-muted small mb-2">💡 Pro Tip</h6>
                    <p class="text-muted small">
                        Create a backup at least <strong>weekly</strong>. Store copies in multiple places. Send to Telegram for reliable cloud backup.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteBackupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-trash text-danger me-2"></i>Delete Backup
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to delete this backup file?</p>
                <p class="small text-muted mb-0" id="deleteBackupFilename"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBackupBtn">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Get base URL dynamically - works on any server
    const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/pages'));
    const apiUrl = baseUrl + '/api.php';
    const deleteModalEl = document.getElementById('deleteBackupModal');
    const deleteModal = (window.bootstrap && deleteModalEl) ? new bootstrap.Modal(deleteModalEl) : null;
    let pendingDeleteFilename = null;

    // Show loading state for buttons
    function setButtonLoading(btnId, loading) {
        const btn = $(btnId);
        if (loading) {
            btn.prop('disabled', true);
            btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
        } else {
            btn.prop('disabled', false);
            btn.html(btn.attr('data-original-html'));
        }
    }

    // Store original button HTML
    $('#downloadBtn').attr('data-original-html', $('#downloadBtn').html());
    $('#telegramBtn').attr('data-original-html', $('#telegramBtn').html());
    $('#bothBtn').attr('data-original-html', $('#bothBtn').html());

    // Show alert
    function showAlert(message, type) {
        const alert = $('#statusAlert');
        alert.removeClass('alert-success alert-danger alert-warning alert-info d-none show');
        alert.addClass(`alert-${type}`);
        
        // Escape HTML to prevent XSS
        const escapedMessage = $('<div>').text(message).html();
        
        const iconClass = {
            'success': 'check-circle',
            'danger': 'exclamation-circle',
            'warning': 'exclamation-triangle',
            'info': 'info-circle'
        }[type] || 'info-circle';
        
        alert.html(`
            <div class="d-flex align-items-start">
                <div class="me-2 mt-1"><i class="bi bi-${iconClass}"></i></div>
                <div class="flex-grow-1">${escapedMessage}</div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);
        alert.removeClass('d-none').addClass('show');
    }

    // Download Backup
    $('#downloadBtn').on('click', function(e) {
        e.preventDefault();
        backupDatabase('download');
    });

    // Send to Telegram
    $('#telegramBtn').on('click', function(e) {
        e.preventDefault();
        backupDatabase('telegram');
    });

    // Both
    $('#bothBtn').on('click', function(e) {
        e.preventDefault();
        backupDatabase('both');
    });

    function backupDatabase(action) {
        // Show progress
        $('#progressSection').removeClass('d-none');
        $('#statusAlert').addClass('d-none');
        
        // Disable all buttons
        $('#downloadBtn, #telegramBtn, #bothBtn').prop('disabled', true);

        $.ajax({
            url: apiUrl + '?endpoint=backupDatabase',
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify({
                action: action
            }),
            contentType: 'application/json',
            success: function(res) {
                $('#progressSection').addClass('d-none');
                
                if (res.status) {
                    showAlert(res.message, 'success');
                    
                    // If download, trigger file download
                    if (action === 'download' || action === 'both') {
                        if (res.data && res.data.filename) {
                            setTimeout(function() {
                                window.location.href = apiUrl + '?endpoint=downloadBackupFile&filename=' + encodeURIComponent(res.data.filename);
                            }, 500);
                        }
                    }
                    
                    // Reload backup history
                    loadBackupHistory();
                } else {
                    const errorMsg = res.message || 'Failed to create backup. Please check your database connection.';
                    showAlert(errorMsg, 'danger');
                }
                
                // Enable buttons
                $('#downloadBtn, #telegramBtn, #bothBtn').prop('disabled', false);
                $('#downloadBtn').html($('#downloadBtn').attr('data-original-html'));
                $('#telegramBtn').html($('#telegramBtn').attr('data-original-html'));
                $('#bothBtn').html($('#bothBtn').attr('data-original-html'));
            },
            error: function(err) {
                $('#progressSection').addClass('d-none');
                let errorMsg = 'Error occurred while creating backup.';
                
                if (err.status === 404) {
                    errorMsg += ' API endpoint not found. Please hard refresh the page (Ctrl+F5).';
                } else if (err.status === 0) {
                    errorMsg += ' Network error. Check your connection.';
                } else if (err.status >= 500) {
                    errorMsg += ' Server error (' + err.status + '). Check browser console for details.';
                } else {
                    errorMsg += ' (Status: ' + err.status + '). Response: ' + err.responseText.substring(0, 100);
                }
                
                showAlert(errorMsg, 'danger');
                console.error('AJAX Error:', err);
                
                $('#downloadBtn, #telegramBtn, #bothBtn').prop('disabled', false);
                $('#downloadBtn').html($('#downloadBtn').attr('data-original-html'));
                $('#telegramBtn').html($('#telegramBtn').attr('data-original-html'));
                $('#bothBtn').html($('#bothBtn').attr('data-original-html'));
            }
        });
    }

    // Load backup history
    function loadBackupHistory() {
        $.ajax({
            url: apiUrl + '?endpoint=getBackupHistory&_=' + Date.now(),
            method: 'GET',
            dataType: 'json',
            cache: false,
            success: function(res) {
                if (res.status && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(backup => {
                        html += `
                            <tr>
                                <td><small>${backup.date}</small></td>
                                <td><small>${backup.filename}</small></td>
                                <td><small>${backup.size}</small></td>
                                <td>
                                    <a href="${apiUrl}?endpoint=downloadBackupFile&filename=${encodeURIComponent(backup.filename)}" class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-backup-btn" data-filename="${backup.filename}" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#backupHistoryTable').html(html);
                } else {
                    $('#backupHistoryTable').html(`
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">
                                <i class="bi bi-inbox"></i> No backups yet
                            </td>
                        </tr>
                    `);
                }
            }
        });
    }
    // Open delete confirmation modal (delegated so it works after table reload)
    $(document).on('click', '.delete-backup-btn', function(e) {
        e.preventDefault();
        const filename = $(this).attr('data-filename');
        pendingDeleteFilename = filename;
        $('#deleteBackupFilename').text(filename);

        if (deleteModal) {
            deleteModal.show();
        } else if (confirm('Delete this backup file? This action cannot be undone.')) {
            deleteBackupFile(filename);
        }
    });

    // Confirm delete in modal
    $('#confirmDeleteBackupBtn').on('click', function() {
        if (!pendingDeleteFilename) return;
        const btn = $(this);
        const originalHtml = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');

        deleteBackupFile(pendingDeleteFilename, function() {
            btn.prop('disabled', false).html(originalHtml);
            if (deleteModal) deleteModal.hide();
            pendingDeleteFilename = null;
            $('#deleteBackupFilename').text('');
        });
    });

    // Delete backup file
    function deleteBackupFile(filename, onComplete) {
        $.ajax({
            url: apiUrl + '?endpoint=deleteBackupFile',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                filename: filename
            }),
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    showAlert(res.message, 'success');
                    loadBackupHistory(); // Reload the list
                } else {
                    showAlert(res.message, 'danger');
                }
                if (typeof onComplete === 'function') onComplete();
            },
            error: function(err) {
                showAlert('Error deleting file. Check console for details.', 'danger');
                console.error('Delete Error:', err);
                if (typeof onComplete === 'function') onComplete();
            }
        });
    }

    // Load history on page load
    loadBackupHistory();

    // Check system requirements
    $('#checkRequirementsBtn').on('click', function(){
        const btn = $(this);
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Checking...');

        $.ajax({
            url: apiUrl + '?endpoint=checkBackupRequirements',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                let html = '<div class="card bg-light">';
                html += '<div class="card-body">';
                
                if (res.data) {
                    const data = res.data;
                    
                    html += '<p><strong>System Requirements:</strong></p>';
                    html += '<ul class="list-unstyled">';
                    
                    // mysqldump check
                    let mysqldumpStatus = data.mysqldump_found 
                        ? '<i class="bi bi-check-circle text-success"></i> ✓ mysqldump found' 
                        : '<i class="bi bi-exclamation-circle text-danger"></i> ✗ mysqldump NOT found';
                    html += '<li class="mb-2">' + mysqldumpStatus;
                    if (data.mysqldump_path) {
                        html += '<br><small class="text-muted">Path: ' + data.mysqldump_path + '</small>';
                    }
                    html += '</li>';
                    
                    // Uploads folder check
                    let uploadsStatus = data.uploads_folder_exists 
                        ? '<i class="bi bi-check-circle text-success"></i> ✓ Uploads folder exists' 
                        : '<i class="bi bi-exclamation-circle text-warning"></i> ℹ Uploads folder will be created';
                    html += '<li class="mb-2">' + uploadsStatus + '</li>';
                    
                    // Writable check
                    let writableStatus = data.uploads_writable 
                        ? '<i class="bi bi-check-circle text-success"></i> ✓ Folder is writable' 
                        : '<i class="bi bi-exclamation-circle text-danger"></i> ✗ Folder is NOT writable';
                    html += '<li class="mb-2">' + writableStatus + '</li>';
                    
                    html += '<li class="mb-2"><small class="text-muted">PHP: ' + data.php_version + '</small></li>';
                    html += '<li class="mb-2"><small class="text-muted">OS: ' + data.os + '</small></li>';
                    
                    html += '</ul>';
                    
                    // Recommendations
                    if (!data.mysqldump_found) {
                        html += '<div class="alert alert-danger mt-3" role="alert">';
                        html += '<strong>⚠️ mysqldump not found!</strong><br>';
                        html += 'Please ensure MySQL is installed in one of these locations:<br>';
                        html += '<code>C:\\xampp\\mysql\\bin\\mysqldump.exe</code><br>';
                        html += 'Or add MySQL\\bin to your system PATH.';
                        html += '</div>';
                    }
                    
                    if (!data.uploads_writable && data.uploads_folder_exists) {
                        html += '<div class="alert alert-warning mt-3" role="alert">';
                        html += '<strong>⚠️ Permission Issue</strong><br>';
                        html += 'The uploads folder exists but is not writable.<br>';
                        html += 'Please check folder permissions.';
                        html += '</div>';
                    }
                }
                
                html += '</div></div>';
                $('#requirementsResult').html(html);
                
                btn.prop('disabled', false);
                btn.html('<i class="bi bi-search me-1"></i> Check System Requirements');
            },
            error: function(){
                $('#requirementsResult').html('<div class="alert alert-danger">Failed to check requirements. Please try again.</div>');
                btn.prop('disabled', false);
                btn.html('<i class="bi bi-search me-1"></i> Check System Requirements');
            }
        });
    });

    // Test API connection
    $('#testApiBtn').on('click', function(){
        const btn = $(this);
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Testing...');

        const testUrl = apiUrl + '?endpoint=getBackupHistory';
        
        $.ajax({
            url: testUrl,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                let html = '<div class="card bg-light border border-success">';
                html += '<div class="card-body">';
                html += '<h6 class="text-success"><i class="bi bi-check-circle me-2"></i>API Connection Working!</h6>';
                html += '<p class="text-muted small mb-2">Test URL: <code>' + testUrl + '</code></p>';
                html += '<p class="text-muted small">Response Status: ' + (res.status ? '<span class="text-success">✓ ' + res.status + '</span>' : 'unknown') + '</p>';
                html += '<p class="text-muted small">Backups Found: ' + (res.data ? res.data.length + ' backup(s)' : '0') + '</p>';
                html += '</div></div>';
                $('#apiTestResult').html(html);
                
                btn.prop('disabled', false);
                btn.html('<i class="bi bi-bug me-1"></i> Test API Connection');
            },
            error: function(err) {
                let html = '<div class="card bg-light border border-danger">';
                html += '<div class="card-body">';
                html += '<h6 class="text-danger"><i class="bi bi-exclamation-circle me-2"></i>API Connection Failed!</h6>';
                html += '<p class="text-muted small mb-2">Test URL: <code>' + testUrl + '</code></p>';
                html += '<p class="text-muted small">Error: HTTP ' + err.status + '</p>';
                html += '<p class="text-danger small"><strong>Solution:</strong> Hard refresh the page with Ctrl+F5 and try again.</p>';
                html += '</div></div>';
                $('#apiTestResult').html(html);
                
                btn.prop('disabled', false);
                btn.html('<i class="bi bi-bug me-1"></i> Test API Connection');
            }
        });
    });

    // Clean old backups
    $('#cleanupBtn').on('click', function(){
        const btn = $(this);
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Cleaning...');

        $.ajax({
            url: apiUrl + '?endpoint=cleanupBackups',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                let html = '<div class="card bg-light border border-success">';
                html += '<div class="card-body">';
                html += '<h6 class="text-success"><i class="bi bi-check-circle me-2"></i>Cleanup Complete!</h6>';
                html += '<p class="text-muted small">Message: ' + (res.message || 'Success') + '</p>';
                if (res.data) {
                    html += '<p class="text-muted small">Files Deleted: <strong>' + (res.data.deleted_count || 0) + '</strong></p>';
                }
                html += '</div></div>';
                $('#cleanupResult').html(html);
                
                // Reload backup history
                loadBackupHistory();
                
                btn.prop('disabled', false);
                btn.html('<i class="bi bi-trash me-1"></i> Clean Old Backups');
            },
            error: function(err) {
                let html = '<div class="card bg-light border border-danger">';
                html += '<div class="card-body">';
                html += '<h6 class="text-danger"><i class="bi bi-exclamation-circle me-2"></i>Cleanup Failed!</h6>';
                html += '<p class="text-muted small">Error: HTTP ' + err.status + '</p>';
                html += '</div></div>';
                $('#cleanupResult').html(html);
                
                btn.prop('disabled', false);
                btn.html('<i class="bi bi-trash me-1"></i> Clean Old Backups');
            }
        });
    });
});


