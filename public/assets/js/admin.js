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
