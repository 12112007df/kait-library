// Администрирование: роли, блокировка, обложки книг

document.querySelectorAll('.role-select').forEach((sel) => {
    sel.addEventListener('change', async () => {
        const userId = sel.closest('tr').dataset.userId;
        const data = await api('user.role', { method: 'POST', body: { user_id: userId, role: sel.value } });
        if (!data.ok) alert(data.error);
    });
});

document.querySelectorAll('.block-check').forEach((chk) => {
    chk.addEventListener('change', async () => {
        const userId = chk.closest('tr').dataset.userId;
        const data = await api('user.block', { method: 'POST', body: { user_id: userId, blocked: chk.checked } });
        if (!data.ok) alert(data.error);
    });
});

document.querySelectorAll('.cover-input').forEach((input) => {
    input.addEventListener('change', async () => {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) {
            alert('Файл больше 2 МБ');
            return;
        }
        const row = input.closest('tr');
        const fd = new FormData();
        fd.append('cover', file);
        fd.append('book_id', row.dataset.bookId);
        const data = await api('cover.upload', { method: 'POST', body: fd });
        row.querySelector('.cover-status').textContent = data.ok ? 'загружено' : data.error;
    });
});
