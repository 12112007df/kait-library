// Страница книги: отзывы и бронирование
const page = document.querySelector('.book-page');
const bookId = page.dataset.bookId;
const reviewsEl = document.getElementById('reviews');

function stars(n) {
    return '★'.repeat(n) + '☆'.repeat(5 - n);
}

async function loadReviews() {
    const data = await api('reviews.list', { params: { book_id: bookId } });
    if (!data.ok) return;
    if (data.items.length === 0) {
        reviewsEl.innerHTML = '<p class="muted">Отзывов пока нет. Будьте первым!</p>';
        return;
    }
    let html = '';
    for (const r of data.items) {
        html += `
            <div class="review">
                <div class="review-head">
                    <b>${r.full_name}</b>
                    <span class="stars">${stars(r.rating)}</span>
                    <span class="muted">${formatDate(r.created_at)}</span>
                </div>
                <div class="review-text">${r.text}</div>
            </div>`;
    }
    reviewsEl.innerHTML = html;
}

const reviewForm = document.getElementById('review-form');
if (reviewForm) {
    reviewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = document.getElementById('review-text').value.trim();
        if (text.length < 3) {
            alert('Отзыв слишком короткий');
            return;
        }
        const data = await api('reviews.add', {
            method: 'POST',
            body: {
                book_id: bookId,
                rating: document.getElementById('rating').value,
                text: text,
            },
        });
        if (data.ok) {
            reviewForm.reset();
            loadReviews();
        } else {
            alert(data.error);
        }
    });
}

const reserveBtn = document.getElementById('reserve-btn');
if (reserveBtn) {
    reserveBtn.addEventListener('click', async () => {
        const data = await api('reserve', { method: 'POST', body: { book_id: bookId } });
        document.getElementById('reserve-msg').textContent = data.message || data.error;
        if (data.ok) reserveBtn.disabled = true;
    });
}

loadReviews();
