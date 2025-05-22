</main> <!-- Закрываем основной контент -->

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>СтройМатериалы</h5>
                <p>Продажа строительных материалов с доставкой по всей стране</p>
            </div>
            <div class="col-md-4">
                <h5>Контакты</h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-telephone"></i> +7 (123) 456-78-90</li>
                    <li><i class="bi bi-envelope"></i> info@stroymat.ru</li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Мы в соцсетях</h5>
                <a href="#" class="text-white me-2"><i class="bi bi-facebook fs-4"></i></a>
                <a href="#" class="text-white me-2"><i class="bi bi-instagram fs-4"></i></a>
                <a href="#" class="text-white"><i class="bi bi-telegram fs-4"></i></a>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <small>&copy; <?= date('Y') ?> СтройМатериалы. Все права защищены.</small>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Скрипт анимации секций -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Плавное появление секций
    const sections = document.querySelectorAll('section');
    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transition = 'opacity 0.5s ease';
    });
    
    setTimeout(() => {
        sections.forEach(section => {
            section.style.opacity = '1';
        });
    }, 300);
});
</script>
</body>
</html>