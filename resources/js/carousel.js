export default () => ({
    rafId: null,

    init() {
        // Pure editorial manual scroll, no initialization needed
    },

    // Premium cinematic easing: easeOutQuart
    // Starts fast for responsiveness, then elegantly and smoothly decelerates
    easeOutQuart(t) {
        return 1 - Math.pow(1 - t, 4);
    },

    scrollCategories(direction) {
        const container = this.$refs.container;
        
        // Cancel any ongoing animation gracefully to avoid jitter/spam clicks
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }

        // Scroll ~28% of the visible width for a subtle, continuous feel
        const scrollAmount = container.clientWidth * 0.28;
        const delta = direction === 'left' ? -scrollAmount : scrollAmount;
        
        const start = container.scrollLeft;
        // Clamp the target scroll to avoid unnecessary over-calculations
        const maxScroll = container.scrollWidth - container.clientWidth;
        const end = Math.max(0, Math.min(start + delta, maxScroll));
        
        // 750ms ensures a very fluid, physical and organic deceleration
        const duration = 750; 
        let startTime = null;

        const animateScroll = (timestamp) => {
            if (!startTime) startTime = timestamp;
            const elapsed = timestamp - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const easeProgress = this.easeOutQuart(progress);
            
            container.scrollLeft = start + (end - start) * easeProgress;

            if (progress < 1) {
                this.rafId = requestAnimationFrame(animateScroll);
            } else {
                this.rafId = null;
            }
        };

        this.rafId = requestAnimationFrame(animateScroll);
    }
});
