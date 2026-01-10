<!-- Time and Term section -->
<section>
    <h3 class="mb-0">Term And Time Management</h3>
    <p class="text-secondary mb-3">Control your study periods and schedules efficiently.</p>

    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 " style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class=" p-0 mt-3 border-top pt-3">
        <div class="table-responsive rounded">
            <div class="d-flex justify-content-between mb-3 align-items-center">
                <p class="m-0 text-secondary fs-4"><span class="px-3 bg-secondary-subtle rounded">Term</span></p>
                <!-- btn add category -->
                <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#addTermModal">
                    Add Term +
                </button> 
            </div>
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <td scope="col" class="text-secondary">#</td>
                        <td scope="col" class="text-secondary">Term</td>
                        <td scope="col" class="text-secondary">Created By</td>
                        <td scope="col" class="text-secondary">Created At</td>
                        <td scope="col" class="text-center text-secondary">Action</td>
                    </tr>
                </thead>
                <tbody id="termsTabletbody">
                    <!-- data -->
                </tbody>
            </table>
        </div>
    </div>

    <div class=" p-0 mt-3 pt-3">
        <div class="table-responsive rounded">
            <div class="d-flex justify-content-between mb-3 align-items-center">
                <p class="m-0 text-primary fs-4"><span class="px-3 bg-primary-subtle rounded">Time</span></p>
                <!-- btn add category -->
                <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#addTimeModal">
                    Add Time +
                </button> 
            </div>
            <table class="table table-bordered align-middle mb-0">
                <thead>
                    <tr>
                        <td scope="col" class="text-secondary">#</td>
                        <td scope="col" class="text-secondary">Time</td>
                        <td scope="col" class="text-secondary">Created By</td>
                        <td scope="col" class="text-secondary">Created At</td>
                        <td scope="col" class="text-center text-secondary">Action</td>
                    </tr>
                </thead>
                <tbody id="timeandtermdata">
                    <!-- data -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Term Modal -->
    <div class="modal fade" id="addTermModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Term</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
            <form id="addTermForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <input type="text" id="term" name="term" class="form-control shadow-none border" placeholder="Enter term" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addTermBtn">Add</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Update Term Modal -->
    <div class="modal fade" id="updateTermModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Term</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateTermForm">
                <div class="modal-body">
                    <input type="hidden" id="updateTermId" name="term_id">
                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <input type="text" class="form-control shadow-none border" id="updateTerm" name="term" placeholder="Enter term" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Delete Term Modal -->
    <div class="modal fade" id="deleteTermModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Delete Term</h6>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
            <form id="deleteTermForm">
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this term?</p>
                    <input type="hidden" id="deleteTermId" name="term_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    
    <!-- Add Time Modal -->
    <div class="modal fade" id="addTimeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Time</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTimeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Time</label>
                        <input type="text" class="form-control shadow-none border" id="time" name="time" required placeholder="Enter Time">
                    </div>            
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Update Time Modal -->
    <div class="modal fade" id="updateTimeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Time</h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="updateTimeForm">
                    <div class="modal-body">
                        <!-- Hidden ID to know which time row is being edited -->
                        <input type="hidden" id="updateTimeId" name="time_id">

                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="text" class="form-control shadow-none border" id="updateTimeValue" name="time" required placeholder="Enter Time">
                        </div>
                      
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Delete Time Modal -->
    <div class="modal fade" id="deleteTimeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Delete Time</h6>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <form id="deleteTimeForm">
                    <div class="modal-body">
                        <p class="mb-0">Are you sure you want to delete this time?</p>
                        <input type="hidden" id="deleteTimeId" name="time_id">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
<!-- Time and Term section -->
<script>
    $(document).ready(function(){

        // message alert
        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true, true).fadeIn(); // Stop previous animations
            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 3000); // Each alert fades out after 3 seconds
        }

        // loadin function
        function setLoading(btn, isLoading) {
            if (isLoading) {
                // Only add spinner if it doesn't exist
                if (btn.find('.spinner-border').length === 0) {
                    btn.prop('disabled', true).append(' <span class="spinner-border spinner-border-sm ms-2" role="status"></span>');
                }
            } else {
                btn.prop('disabled', false);
                btn.find('.spinner-border').remove();
            }
        }


        // Fill table <tbody> with terms
        function fetchTermRows(tbodyId) {
            $.ajax({
                url: 'api.php?endpoint=term_get_all',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    const tbody = $(tbodyId);
                    tbody.empty();

                    if(res.status && res.data.length > 0) {
                        res.data.forEach((term, index) => {
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${term.term}</td>
                                    <td><span class="text-primary bg-primary-subtle px-2 rounded">${term.created_by}</span></td>
                                    <td><span class="text-secondary bg-secondary-subtle px-2 rounded">${term.created_at}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary me-1 updateTermBtn" 
                                                data-id="${term.id}" 
                                                data-term="${term.term}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateTermModal">
                                            <i class="bi bi-pencil"></i>
                                        </button> 
                                        <button class="btn btn-sm btn-danger deleteTermBtn" 
                                                data-id="${term.id}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteTermModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.append(`<tr><td colspan="5" class="text-center text-danger">No terms found</td></tr>`);
                    }
                },
                error: function() {
                    alert('Failed to fetch terms for table.');
                }
            });
        }

        // Delegate click event for dynamically created buttons
        $(document).on('click', '.updateTermBtn', function() {
            const termId = $(this).data('id');
            const termName = $(this).data('term');

            // Fill the modal inputs
            $('#updateTermId').val(termId);
            $('#updateTerm').val(termName);
        });

        // When clicking delete button, fill hidden input in modal
        $(document).on('click', '.deleteTermBtn', function() {
            const termId = $(this).data('id');
            $('#deleteTermId').val(termId); // hidden input in delete modal
        });

        // Add term
        $('#addTermForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $('#addTermBtn');
            const term = $('#term').val();

            setLoading($btn, true); // show spinner

            $.ajax({
                url: 'api.php?endpoint=term_create',
                type: 'POST',
                data: { term: term },
                dataType: 'json',
                success: function(res) {
                    if(res.status) {
                        showAlert(res.message);
                        $('#addTermModal').modal('hide');
                        $('#addTermForm')[0].reset();
                        fetchTermRows('#termsTabletbody'); 
                         // pass the <select> ID
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function() {
                    alert('Something went wrong!');
                },
                complete: function() {
                    setLoading($btn, false); // hide spinner
                }
            });
        });

        // Update Term form submit
        $('#updateTermForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $(this).find('button[type="submit"]');
            setLoading(btn, true);

            const termId = $('#updateTermId').val();
            const termName = $('#updateTerm').val();

            $.ajax({
                url: 'api.php?endpoint=term_update',
                type: 'POST',
                data: { id: termId, term: termName },
                dataType: 'json',
                success: function(res) {
                    setLoading(btn, false);

                    if (res.status) {
                        $('#updateTermModal').modal('hide'); // close modal
                        fetchTermRows('#termsTabletbody');     // refresh table
                        showAlert(res.message);                  // optional: show success
                        fetchTermOptions('#termSelect'); // pass the <select> ID
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    setLoading(btn, false);
                    alert('Failed to update term.');
                }
            });
        });
        
        // Delete Term form submit
        $('#deleteTermForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $(this).find('button[type="submit"]');
            setLoading(btn, true);

            const termId = $('#deleteTermId').val();

            $.ajax({
                url: 'api.php?endpoint=term_delete',
                type: 'POST',
                data: { id: termId },
                dataType: 'json',
                success: function(res) {
                    setLoading(btn, false);

                    if (res.status) {
                        $('#deleteTermModal').modal('hide'); // close modal
                        fetchTermRows('#termsTabletbody');     // refresh table
                        showAlert(res.message);                  // optional: show success
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    setLoading(btn, false);
                    alert('Failed to delete term.');
                }
            });
        });

      
        // Populate table <tbody>
        fetchTermRows('#termsTabletbody'); // pass the table body ID

        // Fetch Time rows and populate table
        function fetchTimeRows(tbodyId) {
            $.ajax({
                url: 'api.php?endpoint=time_get_all',
                type: 'GET',
                dataType: 'json',
                success: function(res) {
                    const tbody = $(tbodyId);
                    tbody.empty();

                    if(res.status && res.data.length > 0) {
                        res.data.forEach((time, index) => {
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${time.time}</td>
                                    <td><span class="text-primary bg-primary-subtle px-2 rounded">${time.created_by}</span></td>
                                    <td><span class="text-secondary bg-secondary-subtle px-2 rounded">${time.created_at}</span></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary me-1 updateTimeBtn" 
                                                data-id="${time.id}" 
                                                data-time="${time.time}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#updateTimeModal">
                                            <i class="bi bi-pencil"></i>
                                        </button> 
                                        <button class="btn btn-sm btn-danger deleteTimeBtn" 
                                                data-id="${time.id}" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteTimeModal">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            `);
                        });
                    }
                    else {
                        tbody.append(`<tr><td colspan="6" class="text-center text-danger">No time entries found</td></tr>`);
                    }
                },
                error: function() {
                    alert('Failed to fetch time entries.');
                }
            });
        }

        // Click Update Time button -> fill modal
        $(document).on('click', '.updateTimeBtn', function() {
            const timeId = $(this).data('id');
            const timeValue = $(this).data('time');

            $('#updateTimeId').val(timeId);
            $('#updateTimeValue').val(timeValue);

            // No more selects for term or class type
        });

        // When clicking delete button, fill hidden input in modal
        $(document).on('click', '.deleteTimeBtn', function() {
            const timeId = $(this).data('id');
            $('#deleteTimeId').val(timeId); // hidden input in delete modal
        });


        // Add Time
        $('#addTimeForm').on('submit', function(e){
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            setLoading(btn, true);

            const timeValue = $('#time').val();

            $.ajax({
                url: 'api.php?endpoint=time_create',
                type: 'POST',
                data: { time: timeValue },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        showAlert(res.message);
                        // $('#addTimeModal').modal('hide');
                        $('#time').val('');
                        fetchTimeRows("#timeandtermdata") 
                    } else {
                        alert(res.message);
                    }
                },
                error: function(){
                    alert('Failed to add time.');
                },
                complete: function(){
                    setLoading(btn, false);
                }
            });
        });

        // Update Time form submit
       $('#updateTimeForm').on('submit', function(e){
            e.preventDefault();
            const btn = $(this).find('button[type="submit"]');
            setLoading(btn, true);

            const timeId = $('#updateTimeId').val();
            const timeValue = $('#updateTimeValue').val();

            $.ajax({
                url: 'api.php?endpoint=time_update',
                type: 'POST',
                data: { 
                    id: timeId, 
                    time: timeValue
                },
                dataType: 'json',
                success: function(res){
                    if(res.status){
                        $('#updateTimeModal').modal('hide');
                        showAlert(res.message);
                        fetchTimeRows('#timeandtermdata'); // Adjust your tbody selector
                    } else {
                        alert(res.message);
                    }
                },
                error: function(){
                    alert('Failed to update time.');
                },
                complete: function(){
                    setLoading(btn, false);
                }
            });
        });

        // Delete Time form submit
        $('#deleteTimeForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $(this).find('button[type="submit"]');
            setLoading(btn, true); // show spinner

            const timeId = $('#deleteTimeId').val();

            $.ajax({
                url: 'api.php?endpoint=time_delete', // your API endpoint for time deletion
                type: 'POST',
                data: { id: timeId },
                dataType: 'json',
                success: function(res) {
                    setLoading(btn, false);

                    if (res.status) {
                        $('#deleteTimeModal').modal('hide');  // close modal
                        fetchTimeRows('#timeandtermdata');    // refresh table
                        showAlert(res.message);               // success alert
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    setLoading(btn, false);
                    alert('Failed to delete time.');
                }
            });
        });

        fetchTimeRows("#timeandtermdata") 
    })
</script>