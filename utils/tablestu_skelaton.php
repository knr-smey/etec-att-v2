<?php
    // tablestu_skeleton.php

    // Function to render skeleton rows
    function renderStudentSkeleton($rows = 2) {
        for ($i = 0; $i < $rows; $i++) {
            echo '<tr>';
            echo '<td><div class="bg-secondary rounded" style="width:20px; height:20px;"></div></td>';
            echo '<td><div class="bg-secondary rounded" style="width:120px; height:20px;"></div></td>';
            echo '<td><div class="bg-secondary rounded" style="width:60px; height:20px;"></div></td>';
            echo '<td><div class="bg-secondary rounded" style="width:80px; height:20px;"></div></td>';
            echo '<td>
                    <div class="p-2 bg-light rounded">
                        <p class="mb-1"><div class="bg-secondary rounded" style="width:40px; height:10px;"></div></p>
                        <p class="mb-1"><div class="bg-secondary rounded" style="width:40px; height:10px;"></div></p>
                        <p class="mb-1"><div class="bg-secondary rounded" style="width:40px; height:10px;"></div></p>
                    </div>
                </td>';
            echo '<td><div class="bg-secondary rounded" style="width:40px; height:20px;"></div></td>';
            echo '<td><div class="bg-secondary rounded" style="width:40px; height:20px;"></div></td>';
            echo '<td><div class="bg-secondary rounded" style="width:40px; height:20px;"></div></td>';
            echo '<td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <div class="bg-secondary rounded" style="width:20px; height:20px;"></div>
                        <div class="bg-secondary rounded" style="width:20px; height:20px;"></div>
                        <div class="bg-secondary rounded" style="width:20px; height:20px;"></div>
                    </div>
                </td>';
            echo '</tr>';
        }
    }

    // Call the function to render skeleton
    renderStudentSkeleton();
?>
