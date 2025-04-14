// Бронирования: отмена
document.querySelectorAll('.cancel-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const row = btn.closest('tr');
        if (!confirm('Отменить бронирование?')) return;
        const data = await api('reservation.cancel', { method: 'POST', body: { id: row.dataset.id } });
        if (data.ok) {
            row.remove();
        } else {
            alert(data.error);
        }
    });
});
