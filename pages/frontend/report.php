<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm rounded-3 overflow-hidden">
                <div class="card-body p-0">
                    <div class="w-full py-4 bg-etec-color text-light text-center">
                        <h3 class="mb-0">Report</h3>
                        <p>We'd love to hear from you. Send us a message!</p>
                    </div>
                    <form id="contactForm" class="p-4">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" name="name" id="name" class="form-control shadow-none" placeholder="Your Name" required>
                        </div>  
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" id="message" class="form-control shadow-none" placeholder="Your Message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-light w-100">
                            <i class="bi bi-send me-1"></i> Send Message
                        </button>
                        <div class="progress mt-3 d-none" id="progressWrapper">
                            <div id="progressBar" 
                                class="progress-bar bg-etec-color progress-bar-striped progress-bar-animated" 
                                style="width: 0%;"></div>
                        </div>
                        <div id="formAlert" class="alert mt-3 d-none" role="alert"></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#contactForm').on('submit', function(e) {
    e.preventDefault();

    // Disable button + show loading text
    $('#sendBtn')
        .prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');

    // Reset & show progress bar
    let progress = 0;
    $('#progressBar').css('width', '0%');
    $('#progressWrapper').removeClass('d-none');

    // Fake smooth progress animation
    let loading = setInterval(() => {
        if (progress < 90) {   
            progress += 10;     
            $('#progressBar').css('width', progress + '%');
        }
    }, 200);

    $.ajax({
        url: 'https://eo5xph9cv3fbbrb.m.pipedream.net', // <-- your Pipedream webhook
        type: 'POST',
        data: $(this).serialize(),

        success: function(response) {
            $('#formAlert')
                .removeClass('d-none alert-danger')
                .addClass('alert-success')
                .text('✅ Message sent successfully!');
            
            $('#contactForm')[0].reset();
        },

        error: function(xhr, status, error) {
            $('#formAlert')
                .removeClass('d-none alert-success')
                .addClass('alert-danger')
                .text('❌ Failed to send message: ' + error);
        },

        complete: function() {
            clearInterval(loading);
            $('#progressBar').css('width', '100%');

            setTimeout(() => {
                $('#progressWrapper').addClass('d-none');
                $('#progressBar').css('width', '0%');
            }, 500);

            $('#sendBtn')
                .prop('disabled', false)
                .html('<i class="bi bi-send me-1"></i> Send Message');
        }
    });
});


</script>
