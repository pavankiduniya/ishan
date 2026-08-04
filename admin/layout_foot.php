        </main>
    </div>
</div>

<!-- Toast notifications -->
<div id="admin-toast" class="toast" role="status" hidden></div>

<script>
// Toast from query params
const params = new URLSearchParams(location.search);
const errorMsg = params.get('error');
const noticeMsg = params.get('notice');
const message = errorMsg || noticeMsg;

if (message) {
    const el = document.getElementById('admin-toast');
    if (el) {
        el.textContent = message;
        if (errorMsg) el.classList.add('error');
        el.hidden = false;
        requestAnimationFrame(() => el.classList.add('show'));

        setTimeout(() => {
            el.classList.remove('show');
            setTimeout(() => { el.hidden = true; }, 300);
        }, 4500);
    }

    params.delete('notice');
    params.delete('error');
    const rest = params.toString();
    history.replaceState({}, '', location.pathname + (rest ? '?' + rest : '') + location.hash);
}
</script>
</body>
</html>
