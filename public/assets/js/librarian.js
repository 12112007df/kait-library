// Рабочее место библиотекаря: выдача и приём книг
const issueForm = document.getElementById('issue-form');
const issueResult = document.getElementById('issue-result');

async function loadReaders() {
    const data = await api('readers');
    if (!data.ok) return;
    const list = document.getElementById('readers');
    list.innerHTML = data.items
        .map((r) => `<option value="${escapeHtml(r.email)}">${escapeHtml(r.full_name)} ${escapeHtml(r.group_name || '')}</option>`)
        .join('');
}

issueForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = document.getElementById('reader-email').value.trim();
    const inventory = document.getElementById('inventory').value.trim().toUpperCase();
    const data = await api('loan.issue', { method: 'POST', body: { email, inventory } });
    if (data.ok) {
        let text = data.message;
        if (data.warnings && data.warnings.length) {
            text += '. Внимание: ' + data.warnings.join('; ');
        }
        showMessage(issueResult, text, 'success');
        issueForm.reset();
        setTimeout(() => location.reload(), 1500);
    } else {
        showMessage(issueResult, data.message || data.error, 'error');
    }
});

document.querySelectorAll('.return-btn').forEach((btn) => {
    btn.addEventListener('click', async () => {
        const row = btn.closest('tr');
        if (!confirm('Принять книгу?')) return;
        const data = await api('loan.return', { method: 'POST', body: { loan_id: row.dataset.loanId } });
        if (data.ok) {
            row.remove();
            if (data.fine > 0) alert(data.message);
        } else {
            alert(data.message || data.error);
        }
    });
});

loadReaders();
