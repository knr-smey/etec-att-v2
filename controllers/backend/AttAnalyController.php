<?php 

class AttAnalyController{
    
    /* ===============================
       RESPONSE HELPER
       =============================== */
    private static function response($status, $message = "", $data = []) {
        echo json_encode([
            "status"  => $status,
            "message" => $message,
            "data"    => $data
        ]);
        exit;
    }

    public static function filter_by_date($conn, $date){

        if(!$date){
            response(false,"Date is required");
        }

        $sql = "
            SELECT 
                COALESCE(SUM(present),0) AS total_present,
                COALESCE(SUM(absent),0) AS total_absent,
                COALESCE(SUM(permission),0) AS total_permission
            FROM student_records
            WHERE DATE(att_record_date) = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        response(true,"Attendance summary",$result);
    }


}