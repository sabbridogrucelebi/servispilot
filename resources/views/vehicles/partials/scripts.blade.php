@if($vehicleImages->count() > 1)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = Array.from(document.querySelectorAll('.vehicle-slide'));
        const prevBtn = document.getElementById('vehicle-prev-slide');
        const nextBtn = document.getElementById('vehicle-next-slide');

        if (!slides.length || !prevBtn || !nextBtn) return;

        let activeIndex = slides.findIndex(slide => !slide.classList.contains('hidden'));
        if (activeIndex < 0) activeIndex = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('hidden', i !== index);
            });
        }

        prevBtn.addEventListener('click', function () {
            activeIndex = (activeIndex - 1 + slides.length) % slides.length;
            showSlide(activeIndex);
        });

        nextBtn.addEventListener('click', function () {
            activeIndex = (activeIndex + 1) % slides.length;
            showSlide(activeIndex);
        });
    });
</script>
@endif

@if($activeTab === 'documents')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('documentModal');
        const openBtn = document.getElementById('openDocumentModal');
        const closeBtn = document.getElementById('closeDocumentModal');
        const closeFooterBtn = document.getElementById('closeDocumentModalFooter');

        if (modal && openBtn) {
            openBtn.addEventListener('click', function () {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (closeFooterBtn) closeFooterBtn.addEventListener('click', closeModal);

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }

        @if($errors->any())
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        @endif
    });
</script>
@endif