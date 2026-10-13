// Portfolio JavaScript - Animations 3D et Interactions

class Portfolio3D {
    constructor() {
        this.scene = null;
        this.camera = null;
        this.renderer = null;
        this.particles = null;
        this.mouse = { x: 0, y: 0 };
        this.windowHalf = { x: window.innerWidth / 2, y: window.innerHeight / 2 };
        
        this.init();
        this.setupEventListeners();
        this.setupScrollAnimations();
        this.setupSkillBars();
        this.setupParallax();
        this.setupCubeControls();
    }

    init() {
        this.initThreeJS();
        this.initLoadingScreen();
        this.initSmoothScrolling();
        this.initNavbarEffects();
    }

    // Three.js 3D Scene
    initThreeJS() {
        const container = document.getElementById('three-container');
        if (!container) return;

        // Scene
        this.scene = new THREE.Scene();
        
        // Camera
        this.camera = new THREE.PerspectiveCamera(
            75, 
            container.clientWidth / container.clientHeight, 
            0.1, 
            1000
        );
        this.camera.position.z = 5;
        
        // Renderer
        this.renderer = new THREE.WebGLRenderer({ 
            alpha: true, 
            antialias: true,
            powerPreference: "high-performance"
        });
        this.renderer.setSize(container.clientWidth, container.clientHeight);
        this.renderer.setClearColor(0x000000, 0);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(this.renderer.domElement);
        
        // Create advanced particle system
        this.createParticleSystem();
        
        // Create floating geometric shapes
        this.createGeometricShapes();
        
        // Start animation loop
        this.animate();
    }

    createParticleSystem() {
        const particleCount = 2000;
        const particlesGeometry = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        const colors = new Float32Array(particleCount * 3);
        const sizes = new Float32Array(particleCount);

        for (let i = 0; i < particleCount; i++) {
            const i3 = i * 3;
            
            // Positions
            positions[i3] = (Math.random() - 0.5) * 20;
            positions[i3 + 1] = (Math.random() - 0.5) * 20;
            positions[i3 + 2] = (Math.random() - 0.5) * 20;
            
            // Colors (white to blue gradient)
            const color = new THREE.Color();
            color.setHSL(0.6, 0.8, Math.random() * 0.5 + 0.5);
            colors[i3] = color.r;
            colors[i3 + 1] = color.g;
            colors[i3 + 2] = color.b;
            
            // Sizes
            sizes[i] = Math.random() * 3 + 1;
        }

        particlesGeometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        particlesGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
        particlesGeometry.setAttribute('size', new THREE.BufferAttribute(sizes, 1));

        const particlesMaterial = new THREE.PointsMaterial({
            size: 0.1,
            vertexColors: true,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending,
            sizeAttenuation: true
        });

        this.particles = new THREE.Points(particlesGeometry, particlesMaterial);
        this.scene.add(this.particles);
    }

    createGeometricShapes() {
        // Create floating geometric shapes
        const shapes = [];
        const geometries = [
            new THREE.BoxGeometry(0.5, 0.5, 0.5),
            new THREE.SphereGeometry(0.3, 16, 16),
            new THREE.ConeGeometry(0.3, 0.8, 8),
            new THREE.TorusGeometry(0.3, 0.1, 8, 16)
        ];

        geometries.forEach((geometry, index) => {
            const material = new THREE.MeshBasicMaterial({
                color: new THREE.Color().setHSL(0.6 + index * 0.1, 0.8, 0.6),
                transparent: true,
                opacity: 0.3,
                wireframe: true
            });

            const shape = new THREE.Mesh(geometry, material);
            shape.position.set(
                (Math.random() - 0.5) * 10,
                (Math.random() - 0.5) * 10,
                (Math.random() - 0.5) * 10
            );
            shape.rotation.set(
                Math.random() * Math.PI,
                Math.random() * Math.PI,
                Math.random() * Math.PI
            );

            shapes.push(shape);
            this.scene.add(shape);
        });

        this.shapes = shapes;
    }

    animate() {
        requestAnimationFrame(() => this.animate());

        const time = Date.now() * 0.001;

        // Animate particles
        if (this.particles) {
            this.particles.rotation.x += 0.0005;
            this.particles.rotation.y += 0.001;
            
            // Mouse interaction
            this.particles.rotation.x += (this.mouse.y * 0.0001);
            this.particles.rotation.y += (this.mouse.x * 0.0001);
        }

        // Animate geometric shapes
        if (this.shapes) {
            this.shapes.forEach((shape, index) => {
                shape.rotation.x += 0.01 * (index + 1);
                shape.rotation.y += 0.005 * (index + 1);
                shape.position.y += Math.sin(time + index) * 0.002;
            });
        }

        // Camera movement
        this.camera.position.x += (this.mouse.x * 0.0005 - this.camera.position.x) * 0.05;
        this.camera.position.y += (-this.mouse.y * 0.0005 - this.camera.position.y) * 0.05;
        this.camera.lookAt(this.scene.position);

        this.renderer.render(this.scene, this.camera);
    }

    // Loading Screen
    initLoadingScreen() {
        const loading = document.createElement('div');
        loading.className = 'loading';
        loading.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(loading);

        window.addEventListener('load', () => {
            setTimeout(() => {
                loading.classList.add('hidden');
                setTimeout(() => {
                    loading.remove();
                }, 500);
            }, 1000);
        });
    }

    // Smooth Scrolling
    initSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Navbar Effects
    initNavbarEffects() {
        const navbar = document.querySelector('.navbar');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // Event Listeners
    setupEventListeners() {
        // Mouse movement for 3D effects
        document.addEventListener('mousemove', (e) => {
            this.mouse.x = e.clientX - this.windowHalf.x;
            this.mouse.y = e.clientY - this.windowHalf.y;
        });

        // Window resize
        window.addEventListener('resize', () => {
            this.windowHalf.x = window.innerWidth / 2;
            this.windowHalf.y = window.innerHeight / 2;

            if (this.camera && this.renderer) {
                this.camera.aspect = window.innerWidth / window.innerHeight;
                this.camera.updateProjectionMatrix();
                this.renderer.setSize(window.innerWidth, window.innerHeight);
            }
        });

        // Intersection Observer for animations
        this.setupIntersectionObserver();
    }

    // Scroll Animations
    setupScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
    }

    // Skill Bars Animation
    setupSkillBars() {
        const skillObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressBars = entry.target.querySelectorAll('.skill-progress');
                    progressBars.forEach((bar, index) => {
                        const width = bar.getAttribute('data-width');
                        setTimeout(() => {
                            bar.style.width = width;
                        }, index * 200);
                    });
                }
            });
        }, { threshold: 0.5 });

        document.querySelectorAll('.skill-item').forEach(item => {
            skillObserver.observe(item);
        });
    }


    // Notification System
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        // Styles
        Object.assign(notification.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            padding: '15px 25px',
            borderRadius: '10px',
            color: 'white',
            fontWeight: '500',
            zIndex: '10000',
            transform: 'translateX(100%)',
            transition: 'transform 0.3s ease',
            background: type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#6366f1'
        });
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Parallax Effects
    setupParallax() {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallaxElements = document.querySelectorAll('.parallax-element');
            
            parallaxElements.forEach((element, index) => {
                const speed = 0.5 + (index * 0.1);
                element.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });
    }

    // Intersection Observer for advanced animations
    setupIntersectionObserver() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -100px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                } else {
                    entry.target.style.animationPlayState = 'paused';
                }
            });
        }, observerOptions);

        // Observe elements with animations
        document.querySelectorAll('[data-aos]').forEach(el => {
            observer.observe(el);
        });
    }

    // Cube Controls
    setupCubeControls() {
        const cube = document.getElementById('aboutCube');
        const cubeWrapper = document.querySelector('.cube-wrapper');
        
        if (!cube || !cubeWrapper) return;

        let rotationX = 0;
        let rotationY = 0;
        const rotationSpeed = 15; // degrees per key press
        let isAnimating = false;

        // Keyboard controls
        document.addEventListener('keydown', (e) => {
            if (isAnimating) return;

            switch(e.key) {
                case 'ArrowUp':
                    e.preventDefault();
                    rotationX -= rotationSpeed;
                    this.rotateCube(cube, rotationX, rotationY);
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    rotationX += rotationSpeed;
                    this.rotateCube(cube, rotationX, rotationY);
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    rotationY -= rotationSpeed;
                    this.rotateCube(cube, rotationX, rotationY);
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    rotationY += rotationSpeed;
                    this.rotateCube(cube, rotationX, rotationY);
                    break;
                case ' ':
                    e.preventDefault();
                    this.resetCube(cube, cubeWrapper);
                    rotationX = 0;
                    rotationY = 0;
                    break;
            }
        });

        // Touch controls for mobile
        let touchStartX = 0;
        let touchStartY = 0;

        cubeWrapper.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        });

        cubeWrapper.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (isAnimating) return;

            const touchEndX = e.touches[0].clientX;
            const touchEndY = e.touches[0].clientY;
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;
            
            rotationY += deltaX * 0.5;
            rotationX -= deltaY * 0.5;
            
            this.rotateCube(cube, rotationX, rotationY);
            
            touchStartX = touchEndX;
            touchStartY = touchEndY;
        });

        // Mouse controls
        let isMouseDown = false;
        let mouseStartX = 0;
        let mouseStartY = 0;

        cubeWrapper.addEventListener('mousedown', (e) => {
            isMouseDown = true;
            mouseStartX = e.clientX;
            mouseStartY = e.clientY;
            cubeWrapper.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isMouseDown || isAnimating) return;

            const deltaX = e.clientX - mouseStartX;
            const deltaY = e.clientY - mouseStartY;
            
            rotationY += deltaX * 0.5;
            rotationX -= deltaY * 0.5;
            
            this.rotateCube(cube, rotationX, rotationY);
            
            mouseStartX = e.clientX;
            mouseStartY = e.clientY;
        });

        document.addEventListener('mouseup', () => {
            isMouseDown = false;
            cubeWrapper.style.cursor = 'grab';
        });

        // Add visual feedback
        cubeWrapper.style.cursor = 'grab';
        cubeWrapper.style.userSelect = 'none';
    }

    rotateCube(cube, rotationX, rotationY) {
        if (!cube) return;

        this.isAnimating = true;
        
        cube.style.transition = 'transform 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
        cube.style.transform = `rotateX(${rotationX}deg) rotateY(${rotationY}deg)`;
        
        setTimeout(() => {
            this.isAnimating = false;
        }, 300);
    }

    resetCube(cube, cubeWrapper) {
        if (!cube || !cubeWrapper) return;

        this.isAnimating = true;
        
        cube.style.transition = 'transform 1s cubic-bezier(0.4, 0, 0.2, 1)';
        cube.style.transform = 'rotateX(0deg) rotateY(0deg)';
        
        // Restart automatic rotation
        cubeWrapper.style.animation = 'none';
        setTimeout(() => {
            cubeWrapper.style.animation = 'cubeRotate 20s infinite linear';
            this.isAnimating = false;
        }, 1000);
    }

    // Utility Methods
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Portfolio3D();
});

// Additional utility functions
const utils = {
    // Generate random number between min and max
    random: (min, max) => Math.random() * (max - min) + min,
    
    // Map value from one range to another
    map: (value, inMin, inMax, outMin, outMax) => {
        return (value - inMin) * (outMax - outMin) / (inMax - inMin) + outMin;
    },
    
    // Easing functions
    easeInOutCubic: (t) => t < 0.5 ? 4 * t * t * t : (t - 1) * (2 * t - 2) * (2 * t - 2) + 1,
    
    // Check if element is in viewport
    isInViewport: (element) => {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
};

// Export for use in other scripts
window.Portfolio3D = Portfolio3D;
window.utils = utils;
