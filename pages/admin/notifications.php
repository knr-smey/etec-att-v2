<!-- Notification section -->
<section id="notificationSection">

    <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
        <div>
            <h3 class="mb-0">Notification</h3>
            <p class="text-secondary mb-0">Waiting for new registration.</p>
        </div>  
        <!-- Button to fetch pending users -->
        <button id="btnFetchUsers" class="btn btn-primary btn-sm">Fetch Pending Users</button>              
    </div>

    <div id="pendingUsersContainer" class="mt-3">
        <!-- Pending users will be appended here -->
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function(){

        // Function to fetch pending users
        function fetchPendingUsers() {
            $.ajax({
                url: 'api.php?endpoint=getPendingUsers',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    if(res.status && res.data.length > 0){
                        let html = '';
                        res.data.forEach(user => {
                            html += `
                                <div class="alert alert-primary alert-dismissible fade show d-flex justify-content-between align-items-center pe-3" role="alert">
                                    <div>
                                        ☑️ New Instructor registered: <strong>${user.name}</strong>
                                    </div>
                                    <div>
                                        <button class="btn btn-sm btn-secondary" onclick="rejectInstructor(${user.id})">
                                            <i class="bi bi-ban me-1"></i> Reject
                                        </button>
                                        <button class="btn btn-sm btn-primary" onclick="approveInstructor(${user.id})">
                                            <i class="bi bi-check2-circle me-1"></i> Approve
                                        </button>
                                    </div>
                                </div>
                            `;
                        });
                        $('#pendingUsersContainer').html(html);
                    } else {
                        $('#pendingUsersContainer').html('<p class="text-danger alert alert-danger">No new registrations.</p>');
                    }
                },
                error: function(){
                    $('#pendingUsersContainer').html('<p class="text-danger alert alert-danger">Failed to fetch data.</p>');
                }
            });
        }

        // Fetch users when button is clicked
        $('#btnFetchUsers').click(fetchPendingUsers);

        // Approve instructor function
        window.approveInstructor = function(userId){
            $.ajax({
                url: 'api.php?endpoint=updateApproval',
                type: 'POST',
                data: { user_id: userId, approval: 'approved' },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        fetchPendingUsers(); // Refresh list after approval
                    } else {
                        alert(res.message);
                    }
                },
                error: function(){
                    alert("Failed to update approval");
                }
            });
        }

        // Optional: Reject instructor function
        window.rejectInstructor = function(userId){
            $.ajax({
                url: 'api.php?endpoint=updateApproval',
                type: 'POST',
                data: { user_id: userId, approval: 'rejected' },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        fetchPendingUsers(); // Refresh list after rejection
                    } else {
                        alert(res.message);
                    }
                },
                error: function(){
                    alert("Failed to update rejection");
                }
            });
        }

    });
</script>
