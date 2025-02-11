// Обёртка над fetch для обращения к api.php
const API_URL = 'api.php';

async function api(action, { method = 'GET', params = {}, body = null } = {}) {
    const query = new URLSearchParams({ action, ...params }).toString();
    const options = { method, headers: {} };
    if (body instanceof FormData) {
        options.body = body;
    } else if (body !== null) {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
    }
    const response = await fetch(`${API_URL}?${query}`, options);
    let data;
    try {
        data = await response.json();
    } catch (e) {
        console.log('Ответ сервера не JSON', e);
        data = { ok: false, error: 'Ошибка сервера' };
    }
    return data;
}

function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function formatDate(iso) {
    if (!iso) return '—';
    const [y, m, d] = iso.substring(0, 10).split('-');
    return `${d}.${m}.${y}`;
}

function showMessage(el, text, type = 'success') {
    el.innerHTML = `<div class="alert alert-${type}">${escapeHtml(text)}</div>`;
}
