<!-- Schedule Section -->
<section>
<h3 class="mb-0">Schedule Management</h3>
<p class="text-secondary mb-3">Easily manage and organize your study periods and time slots.</p>

<div class=p-0 mt-3 border-top pt-3">
    <!-- Success Alert -->
    <div id="successAlert" class="alert alert-success alert-dismissible fade show mt-3 " style="display:none;" role="alert">
        <span id="successMessage"></span>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div class="table-responsive">
        <div class="d-flex justify-content-between mb-3 align-items-center">
            <form class="col-3">
                <select name="" id="filterClassType" class="form-select shadow-none border">
                    
                    <!-- Populate dynamically -->
                </select>
            </form>
            <button class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
                Add Schedule +
            </button>
        </div>

        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <td class="text-secondary">Class Type</td>
                    <td class="text-secondary">Term</td>
                    <td class="text-secondary" style="width: 500px;">Time Slots</td>
                    <td class="text-secondary text-center">Actions</td>
                </tr>
            </thead>
            <tbody id="schedulesData">
                
            </tbody>
        </table>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Schedule</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="addScheduleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Class Type</label>
                        <select id="class_type" name="class_type" class="form-select border shadow-none" required>                           
                            <!-- Populate dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Term</label>
                        <select id="term_id" name="term_id" class="form-select border shadow-none" required>                 
                            <!-- Populate dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Time Slots</label>
                        <div id="addTimeSlots">
                            <!-- Add more as needed -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addBtn">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Delete Schedule Modal -->
<div class="modal fade" id="deleteScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Delete Schedule</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteScheduleForm">
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this schedule?</p>
                    <input type="hidden" id="deleteScheduleId" name="schedule_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="btnDeleteSchedule">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Term Modal -->
<div class="modal fade" id="deleteTerm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Delete Term</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteTermForm">
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete this therm?</p>
                    <input type="hidden" id="term_id_del" name="term_id">
                    <input type="hidden" id="class_type_id" name="class_type_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="btnDeleteTerm">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

</section>
<script>
    $(document).ready(function(){

        let allSchedules = [];

        // loading function
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

        // showAlert function
        function showAlert(message) {
            $('#successMessage').text(message);
            $('#successAlert').stop(true, true).fadeIn(); // Stop previous animations
            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 3000); // Each alert fades out after 3 seconds
        }

        // function getClassTypes
        function getClassTypes(){
            $.ajax({
                url: 'api.php?endpoint=class_type_get_all', // adjust URL if needed
                method: 'GET',
                dataType: 'json',
                success: function(response){
                    // console.log("Class Types fetched", response);
                    if(response.status){
                        let select = $("#class_type");
                        let filterClassType = $("#filterClassType");

                        select.empty(); // clear previous options
                        filterClassType.empty(); // clear previous options

                        select.append('<option value="" disabled selected>Select Class Type</option>');
                        filterClassType.append('<option value="" disabled selected>Filter Class Type</option>');
                        filterClassType.append('<option value="">All Class Type</option>');
                        
                        response.data.forEach(function(item){
                            select.append(
                                `<option value="${item.id}">${item.class_type}</option>`
                            );
                            filterClassType.append(
                                `<option value="${item.id}">${item.class_type}</option>`
                            );
                        });
                    } else {
                        console.log(response.message || "Failed to load class types");
                    }
                },
                error: function(){
                    alert("An error occurred while fetching class types");
                }
            });
        }
     
        // function getClassTypes
        function getClassTerms(){
            $.ajax({
                url: 'api.php?endpoint=term_get_all', // adjust URL if needed
                method: 'GET',
                dataType: 'json',
                success: function(response){
                    // console.log( response);
                    if(response.status){
                        let select = $("#term_id");
                        select.empty(); // clear previous options
                        select.append('<option value="" disabled selected>Select Class Type</option>');
                        
                        response.data.forEach(function(item){
                            select.append(
                                `<option value="${item.id}">${item.term}</option>`
                            );
                        });
                    } else {
                        console.log(response.message || "Failed to load class types");
                    }
                },
                error: function(){
                    alert("An error occurred while fetching class types");
                }
            });
        }

        // function getTimeSlots
        function getTimeSlots(){
            $.ajax({
                url: 'api.php?endpoint=time_get_all',
                method: 'GET',
                dataType: 'json',
                success: function(response){
                    //  console.log( response);
                    if(response.status){
                        let container = $("#addTimeSlots");
                        container.empty();

                        response.data.forEach(function(item){
                            container.append(`
                                <div class="d-flex justify-content-between align-items-center border px-3 py-2">
                                    <span>${item.time}</span>
                                    <input type="checkbox" class="form-check" 
                                        name="time_slots[]" 
                                        value="${item.id}">
                                </div>
                            `);
                        });
                    }
                }
            });
        }

        function renderSchedules(data) {
            let tbody = $("#schedulesData");
            tbody.empty();

            if (!data || data.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="4" class="text-center">No schedules available</td>
                    </tr>
                `);
                return;
            }

            // Group by class type
            let grouped = {};
            data.forEach(item => {
                const classType = item.class_type_name;
                if (!grouped[classType]) grouped[classType] = [];
                grouped[classType].push(item);
            });

            for (let classType in grouped) {
                const rows = grouped[classType];
                const rowspan = rows.length;

                rows.forEach((row, index) => {
                    let tr = $("<tr></tr>");

                    if (index === 0) {
                        tr.append(`<td rowspan="${rowspan}">${row.class_type_name}</td>`);
                    }

                    tr.append(`<td>
                                <div class="d-flex justify-content-between">
                                    <span>${row.term_name}</span>
                                    <button data-class_type_id="${row.class_type_id}" data-term_id="${row.term_id}" class="btn btn-sm btn-outline-danger btn-delete-term" data-bs-toggle="modal" data-bs-target="#deleteTerm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>`); 
                    // Convert comma-separated string into spans
                    const timeSlotsHtml = row.time_slots
                        .split(',')                    // split string into array
                        .map(slot => slot.trim())      // remove extra spaces
                        .map(slot => `<span class="text-secondary bg-secondary-subtle px-2 rounded me-1">${slot}</span>`)
                        .join('');

                    tr.append(`
                                <td>
                                    <div style="max-width: 500px; overflow-x: auto; white-space: nowrap;">
                                        ${timeSlotsHtml}
                                    </div>
                                </td>
                            `);

                    if (index === 0) {
                        tr.append(`
                            <td rowspan="${rowspan}" class="text-center">
                                <button data-id="${row.class_type_id}" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteScheduleModal">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        `);
                    }

                    tbody.append(tr);
                });
            }
        }


        function getSchedules() {
            $.ajax({
                url: 'api.php?endpoint=schedule_getall',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    // console.log(response.data); // debug first
                    if(response.status){
                        allSchedules = response.data;     
                        renderSchedules(response.data);
                    } else {
                        console.log(response.message || "Failed to fetch schedules");
                    }
                },
                error: function(err){
                    console.error("Error fetching schedules", err);
                }
            });
        }


        $(document).on('change', '#filterClassType', function() {
            const selectedId = $(this).val();

            if (!selectedId) {
                renderSchedules(allSchedules);  // show all if nothing selected
                return;
            }

            const filtered = allSchedules.filter(item => item.class_type_id == selectedId);
            renderSchedules(filtered);  // ✅ only show filtered data
        });

        $(document).on('click', '.btn-delete', function() {
            const scheduleId = $(this).data('id');     // Get the schedule ID from button
            $('#deleteScheduleId').val(scheduleId);   // Set it to hidden input in modal
            alert(scheduleId)
        });

        $(document).on('click', '.btn-delete-term', function() {
            const term_id = $(this).data('term_id');
            const class_type_id = $(this).data('class_type_id');     // Get the schedule ID from button
            // $('#deleteScheduleId').val(scheduleId);   // Set it to hidden input in modal

            $('#term_id_del').val(term_id); 
            $('#class_type_id').val(class_type_id); 

        });

        getSchedules();
        getTimeSlots();
        getClassTypes();
        getClassTerms();

        $("#addScheduleForm").on("submit", function(e){
            e.preventDefault();

            let btn = $('#addBtn');
            let class_type_id = $("#class_type").val();
            let term_id = $("#term_id").val();
            let time_ids = $("input[name='time_slots[]']:checked")
                .map(function(){ return $(this).val(); })
                .get(); // => [1, 2, 3]

            if(time_ids.length === 0){
                alert("Please select at least one time slot.");
                return;
            }

            setLoading(btn,true)
            $.ajax({
                url: 'api.php?endpoint=schedule_create',
                method: 'POST',
                dataType: 'json',
                data: {
                    class_type_id: class_type_id,
                    term_id: term_id,
                    time_ids: time_ids // send as array
                },
                success: function(response){
                    if(response.status){
                        setLoading(btn,false)
                        showAlert(response.message);
                        // alert("Schedule(s) created successfully!");
                        $("#addScheduleModal").modal('hide');
                        getSchedules();
                        $("#addScheduleForm")[0].reset();
                    } else {
                        alert(response.message || "Failed to create schedule");
                    }
                },
                error: function(err){
                    console.error("Error creating schedule", err);
                }
            });
        });

        $('#deleteScheduleForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $('#btnDeleteSchedule');
            const scheduleId = $('#deleteScheduleId').val();

            if(!scheduleId){
                alert("Schedule ID not found!");
                return;
            }
            setLoading(btn,true)
            $.ajax({
                url: 'api.php?endpoint=schedule_delete',
                method: 'POST',
                data: { id: scheduleId },
                dataType: 'json',
                success: function(response){
                    if(response.status){
                        setLoading(btn,false)
                        showAlert(response.message);
                        // alert("Schedule deleted successfully!");
                        $('#deleteScheduleModal').modal('hide');
                        getSchedules(); // refresh table
                    } else {
                        alert(response.message || "Failed to delete schedule");
                    }
                },
                error: function(err){
                    console.error("Error deleting schedule", err);
                }
            });
        });


        $('#deleteTermForm').on('submit', function(e) {
            e.preventDefault();

            const btn = $('#btnDeleteTerm');
            const  termId = $('#term_id_del').val();
            const  classTypeId = $('#class_type_id').val();

            if (!classTypeId || !termId) {
                alert("Class Type ID and Term ID are required!");
                return;
            }
            setLoading(btn,true)
            $.ajax({
                url: 'api.php?endpoint=schedule_term_delete',
                method: 'POST',
                data: { 
                    class_type_id: classTypeId, 
                    term_id: termId 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status) {
                        setLoading(btn,false)
                        showAlert(response.message);
                        // alert(response.message);
                        $('#deleteTerm').modal('hide');
                        getSchedules(); // refresh table
                    } else {
                        alert(response.message || "Failed to delete schedules.");
                    }
                },
                error: function(err) {
                    console.error("Error deleting schedules", err);
                }
            });
        });


        
    })
</script>