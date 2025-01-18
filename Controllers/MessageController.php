<?php
class MessageController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function markAsRead() {
        try {
            if (empty($_POST['message_id'])) {
                throw new Exception('ID pesan tidak valid');
            }

            $query = "UPDATE kontak SET status = 'read' WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([$_POST['message_id']])) {
                throw new Exception('Gagal mengupdate status pesan');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Status pesan berhasil diupdate'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function deleteMessage() {
        try {
            if (empty($_POST['message_id'])) {
                throw new Exception('ID pesan tidak valid');
            }

            $query = "DELETE FROM kontak WHERE id = ?";
            $stmt = $this->db->prepare($query);
            
            if (!$stmt->execute([$_POST['message_id']])) {
                throw new Exception('Gagal menghapus pesan');
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Pesan berhasil dihapus'
            ]);

        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
} 