// Каталог: поиск, фильтр, сортировка, постраничный вывод
const form = document.getElementById('search-form');
const booksEl = document.getElementById('books');
const pagerEl = document.getElementById('pager');
const foundEl = document.getElementById('found');

let currentPage = 1;

function bookCard(book) {
    const cover = book.cover
        ? `<img src="uploads/${escapeHtml(book.cover)}" alt="">`
        : `<div class="cover-placeholder">${escapeHtml(book.title.charAt(0))}</div>`;
    const badge = book.available > 0
        ? `<span class="badge badge-ok">в наличии: ${book.available}</span>`
        : `<span class="badge badge-no">нет в наличии</span>`;
    return `
        <a class="book-card" href="index.php?page=book&id=${book.id}">
            ${cover}
            <div class="book-card-body">
                <div class="book-title">${escapeHtml(book.title)}</div>
                <div class="muted">${escapeHtml(book.author)}${book.year ? ', ' + book.year : ''}</div>
                <div class="muted">${escapeHtml(book.genre || '')}</div>
                ${badge}
            </div>
        </a>`;
}

function renderPager(pages, page) {
    pagerEl.innerHTML = '';
    if (pages <= 1) return;
    for (let i = 1; i <= pages; i++) {
        const b = document.createElement('button');
        b.textContent = i;
        b.className = 'btn btn-small' + (i === page ? '' : ' btn-light');
        b.addEventListener('click', () => load(i));
        pagerEl.appendChild(b);
    }
}

async function load(page = 1) {
    currentPage = page;
    const params = {
        q: document.getElementById('q').value,
        genre: document.getElementById('genre').value,
        sort: document.getElementById('sort').value,
        p: page,
    };
    booksEl.innerHTML = '<p class="muted">Загрузка…</p>';
    const data = await api('books.search', { params });
    if (!data.ok) {
        booksEl.innerHTML = `<p class="alert alert-error">${escapeHtml(data.error)}</p>`;
        return;
    }
    foundEl.textContent = `Найдено книг: ${data.total}`;
    booksEl.innerHTML = data.items.map(bookCard).join('') || '<p class="muted">Ничего не найдено</p>';
    renderPager(data.pages, data.page);
}

form.addEventListener('submit', (e) => {
    e.preventDefault();
    load(1);
});

document.getElementById('genre').addEventListener('change', () => load(1));
document.getElementById('sort').addEventListener('change', () => load(1));

load();

const exportButton = document.getElementById('export-catalog');
const exportStatus = document.getElementById('export-status');

if (exportButton && exportStatus) {
    exportButton.addEventListener('click', async () => {
        const params = new URLSearchParams({
            action: 'books.export',
            q: document.getElementById('q').value,
            genre: document.getElementById('genre').value,
            sort: document.getElementById('sort').value,
        });
        exportButton.disabled = true;
        exportStatus.textContent = 'Подготовка файла…';
        let downloadUrl = null;
        try {
            const response = await fetch(`api.php?${params}`, {
                credentials: 'same-origin',
                headers: { Accept: 'text/csv' },
            });
            if (!response.ok) {
                const error = await response.json().catch(() => null);
                throw new Error(error?.error || 'Не удалось выгрузить каталог.');
            }
            if (!response.headers.get('content-type')?.startsWith('text/csv')) {
                throw new Error('Сервер вернул неподдерживаемый формат файла.');
            }
            const blob = await response.blob();
            downloadUrl = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = 'catalog.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            exportStatus.textContent = 'Файл catalog.csv подготовлен.';
        } catch (error) {
            exportStatus.textContent = error.message;
        } finally {
            if (downloadUrl) {
                setTimeout(() => URL.revokeObjectURL(downloadUrl), 1000);
            }
            exportButton.disabled = false;
        }
    });
}
