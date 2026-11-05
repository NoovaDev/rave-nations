<!-- 3D Interactive Holographic Ticket Component -->
<div class="ticket-wrapper" id="ticketCard">
    <div class="ticket">
        <!-- Main Content Stub -->
        <div class="ticket-main">
            <div class="ticket-header">
                <div class="brand-title">RAVE<span>NATIONS</span></div>
                <div class="vip-badge">VIP PASS</div>
            </div>
            <div class="event-info">
                <h3>MIDNIGHT EDM FEST</h3>
                <div class="event-details">
                    <span>DATE: <span>DEC 31</span></span>
                    <span>GATE: <span>GATE 04</span></span>
                </div>
            </div>
        </div>
        <!-- Barcode Stub -->
        <div class="ticket-stub">
            <div class="ticket-id">#RN2026</div>
            <div class="barcode"></div>
            <div class="stub-price">LKR 4,500</div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const card = document.getElementById('ticketCard');
        if (!card) return;

        // Smooth 3D tilt tracking based on mouse movement
        document.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;

            const rotateX = (-y / (rect.height / 2)) * 12;
            const rotateY = (x / (rect.width / 2)) * 12;

            card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.03, 1.03, 1.03)`;
            card.style.setProperty('--glow-opacity', '0.8');
        });

        // Reset position on mouse leave
        document.addEventListener('mouseleave', () => {
            card.style.transform = 'rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            card.style.setProperty('--glow-opacity', '0.3');
        });
    });
</script>