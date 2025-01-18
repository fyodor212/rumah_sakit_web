function exportBookings() {
    // Implementation for exporting bookings data
    window.location.href = '?page=admin/export&type=bookings';
}

function exportPayments() {
    // Implementation for exporting payments data
    window.location.href = '?page=admin/export&type=payments';
}

function printBookings() {
    window.print();
}

function generateReport() {
    window.location.href = '?page=admin/reports';
} 