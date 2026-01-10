<?php for ($i = 0; $i < 3; $i++): ?>
<div class="col-md-4 mb-3">
    <div class="card shadow-sm border rounded h-100">
        <div class="card-body p-3">

            <!-- Header -->
            <div class="d-flex align-items-center mb-3 justify-content-between">
                <div class="d-flex align-items-center col-10">
                    <div class="placeholder rounded-circle me-2" style="width: 40px; height: 40px;"></div>
                    <div class="placeholder col-7"></div>
                </div>
                <div class="placeholder rounded-circle" style="width: 20px; height: 20px;"></div>
            </div>

            <!-- Lines -->
            <?php 
            $lines = [6, 8, 5, 4, 6, 8, 3]; // width in cols
            foreach($lines as $line): ?>
                <div class="placeholder-glow mb-2 pb-2 border-bottom">
                    <div class="placeholder col-<?= $line ?>"></div>
                </div>
            <?php endforeach; ?>

            <!-- Button -->
            <div class="placeholder-glow mt-3">
                <div class="placeholder col-12 rounded py-2"></div>
            </div>

        </div>
    </div>
</div>
<?php endfor; ?>
