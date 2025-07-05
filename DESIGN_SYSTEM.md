# FractalMerch Design System Premium

## 🎨 Design Tokens

### Color Palette (Emotional Design)
```css
:root {
  /* Primary Brand Colors */
  --fractal-primary: #FF9500;        /* Naranja principal - Energía y creatividad */
  --fractal-secondary: #232F3E;      /* Azul oscuro - Profesionalismo */
  --fractal-accent: #0066c0;         /* Azul confianza - Amazon-style */
  
  /* Emotional Colors (Aarron Walter) */
  --trust-blue: #0066c0;             /* Confianza en checkout */
  --delight-coral: #FF6B6B;          /* Momentos de deleite */
  --success-green: #4CAF50;          /* Confirmaciones positivas */
  --warmth-orange: #FF9500;          /* Calidez de marca */
  --focus-purple: #8B5CF6;           /* Estados de foco */
  
  /* Semantic Colors */
  --success: #10B981;
  --warning: #F59E0B;
  --error: #EF4444;
  --info: #3B82F6;
  
  /* Neutral Palette */
  --gray-50: #F9FAFB;
  --gray-100: #F3F4F6;
  --gray-200: #E5E7EB;
  --gray-300: #D1D5DB;
  --gray-400: #9CA3AF;
  --gray-500: #6B7280;
  --gray-600: #4B5563;
  --gray-700: #374151;
  --gray-800: #1F2937;
  --gray-900: #111827;
  
  /* Dark Mode Support */
  --bg-primary: var(--gray-50);
  --bg-secondary: var(--gray-100);
  --text-primary: var(--gray-900);
  --text-secondary: var(--gray-600);
  --border-color: var(--gray-200);
}

[data-theme="dark"] {
  --bg-primary: var(--gray-900);
  --bg-secondary: var(--gray-800);
  --text-primary: var(--gray-50);
  --text-secondary: var(--gray-300);
  --border-color: var(--gray-700);
}
```

### Typography Scale
```css
:root {
  /* Font Families */
  --font-brand: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-body: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  --font-mono: 'JetBrains Mono', 'Fira Code', monospace;
  
  /* Font Sizes (Modular Scale - 1.250) */
  --text-xs: 0.75rem;      /* 12px */
  --text-sm: 0.875rem;     /* 14px */
  --text-base: 1rem;       /* 16px */
  --text-lg: 1.125rem;     /* 18px */
  --text-xl: 1.25rem;      /* 20px */
  --text-2xl: 1.5rem;      /* 24px */
  --text-3xl: 1.875rem;    /* 30px */
  --text-4xl: 2.25rem;     /* 36px */
  --text-5xl: 3rem;        /* 48px */
  --text-6xl: 3.75rem;     /* 60px */
  
  /* Font Weights */
  --font-light: 300;
  --font-normal: 400;
  --font-medium: 500;
  --font-semibold: 600;
  --font-bold: 700;
  --font-extrabold: 800;
  
  /* Line Heights */
  --leading-tight: 1.25;
  --leading-snug: 1.375;
  --leading-normal: 1.5;
  --leading-relaxed: 1.625;
  --leading-loose: 2;
}
```

### Spacing System (8px Grid)
```css
:root {
  /* Spacing Scale */
  --space-px: 1px;
  --space-0: 0;
  --space-1: 0.25rem;      /* 4px */
  --space-2: 0.5rem;       /* 8px */
  --space-3: 0.75rem;      /* 12px */
  --space-4: 1rem;         /* 16px */
  --space-5: 1.25rem;      /* 20px */
  --space-6: 1.5rem;       /* 24px */
  --space-8: 2rem;         /* 32px */
  --space-10: 2.5rem;      /* 40px */
  --space-12: 3rem;        /* 48px */
  --space-16: 4rem;        /* 64px */
  --space-20: 5rem;        /* 80px */
  --space-24: 6rem;        /* 96px */
  --space-32: 8rem;        /* 128px */
  --space-40: 10rem;       /* 160px */
  --space-48: 12rem;       /* 192px */
  --space-56: 14rem;       /* 224px */
  --space-64: 16rem;       /* 256px */
}
```

### Responsive Breakpoints (Ethan Marcotte)
```css
:root {
  /* Mobile First Breakpoints */
  --breakpoint-xs: 320px;    /* Extra small devices */
  --breakpoint-sm: 640px;    /* Small devices */
  --breakpoint-md: 768px;    /* Medium devices */
  --breakpoint-lg: 1024px;   /* Large devices */
  --breakpoint-xl: 1280px;   /* Extra large devices */
  --breakpoint-2xl: 1536px;  /* 2X large devices */
}

/* Media Query Mixins */
@media (min-width: 640px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
@media (min-width: 1536px) { /* 2xl */ }
```

### Animation & Transitions (Microinteractions)
```css
:root {
  /* Animation Durations */
  --duration-fast: 150ms;
  --duration-normal: 300ms;
  --duration-slow: 500ms;
  --duration-slower: 750ms;
  
  /* Easing Functions */
  --ease-linear: linear;
  --ease-in: cubic-bezier(0.4, 0, 1, 1);
  --ease-out: cubic-bezier(0, 0, 0.2, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
  
  /* Common Transforms */
  --hover-lift: translateY(-2px);
  --hover-scale: scale(1.05);
  --focus-glow: 0 0 0 3px rgba(66, 153, 225, 0.3);
  
  /* Shadows */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-base: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
```

## 🧩 Component Library (Atomic Design - Brad Frost)

### Atoms

#### Button Components
```css
/* Base Button */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-2) var(--space-4);
  border: 1px solid transparent;
  border-radius: 0.375rem;
  font-family: var(--font-body);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  line-height: var(--leading-tight);
  text-decoration: none;
  transition: all var(--duration-fast) var(--ease-out);
  cursor: pointer;
  user-select: none;
}

.btn:focus {
  outline: none;
  box-shadow: var(--focus-glow);
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Button Variants */
.btn-primary {
  background-color: var(--fractal-primary);
  color: white;
  border-color: var(--fractal-primary);
}

.btn-primary:hover {
  background-color: color-mix(in srgb, var(--fractal-primary) 90%, black);
  transform: var(--hover-lift);
}

.btn-secondary {
  background-color: transparent;
  color: var(--fractal-primary);
  border-color: var(--fractal-primary);
}

.btn-secondary:hover {
  background-color: var(--fractal-primary);
  color: white;
  transform: var(--hover-lift);
}

.btn-success {
  background-color: var(--success-green);
  color: white;
  border-color: var(--success-green);
}

.btn-trust {
  background-color: var(--trust-blue);
  color: white;
  border-color: var(--trust-blue);
}

/* Button Sizes */
.btn-sm {
  padding: var(--space-1) var(--space-3);
  font-size: var(--text-xs);
}

.btn-lg {
  padding: var(--space-3) var(--space-6);
  font-size: var(--text-lg);
}

.btn-xl {
  padding: var(--space-4) var(--space-8);
  font-size: var(--text-xl);
  font-weight: var(--font-semibold);
}

/* Icon Buttons */
.btn-icon {
  padding: var(--space-2);
  width: var(--space-10);
  height: var(--space-10);
}

.btn-icon svg {
  width: var(--space-5);
  height: var(--space-5);
}
```

#### Input Components
```css
.form-input {
  display: block;
  width: 100%;
  padding: var(--space-3);
  border: 1px solid var(--border-color);
  border-radius: 0.375rem;
  font-family: var(--font-body);
  font-size: var(--text-base);
  line-height: var(--leading-tight);
  background-color: var(--bg-primary);
  color: var(--text-primary);
  transition: all var(--duration-fast) var(--ease-out);
}

.form-input:focus {
  outline: none;
  border-color: var(--fractal-primary);
  box-shadow: 0 0 0 3px rgba(255, 149, 0, 0.1);
}

.form-input:invalid {
  border-color: var(--error);
}

.form-label {
  display: block;
  margin-bottom: var(--space-2);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--text-primary);
}

.form-error {
  margin-top: var(--space-1);
  font-size: var(--text-sm);
  color: var(--error);
}

.form-helper {
  margin-top: var(--space-1);
  font-size: var(--text-sm);
  color: var(--text-secondary);
}
```

### Molecules

#### Product Card
```css
.product-card {
  background: var(--bg-primary);
  border: 1px solid var(--border-color);
  border-radius: 0.75rem;
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: all var(--duration-normal) var(--ease-out);
  position: relative;
}

.product-card:hover {
  transform: var(--hover-lift);
  box-shadow: var(--shadow-lg);
}

.product-card__image {
  aspect-ratio: 1 / 1;
  overflow: hidden;
  background: var(--gray-100);
}

.product-card__image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform var(--duration-slow) var(--ease-out);
}

.product-card:hover .product-card__image img {
  transform: scale(1.05);
}

.product-card__content {
  padding: var(--space-4);
}

.product-card__title {
  font-size: var(--text-lg);
  font-weight: var(--font-semibold);
  color: var(--text-primary);
  margin-bottom: var(--space-2);
  line-height: var(--leading-tight);
}

.product-card__price {
  font-size: var(--text-xl);
  font-weight: var(--font-bold);
  color: var(--fractal-primary);
  margin-bottom: var(--space-3);
}

.product-card__badge {
  position: absolute;
  top: var(--space-3);
  right: var(--space-3);
  padding: var(--space-1) var(--space-2);
  background: var(--delight-coral);
  color: white;
  font-size: var(--text-xs);
  font-weight: var(--font-semibold);
  border-radius: 9999px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}
```

#### Navigation Header
```css
.nav-header {
  background: var(--bg-primary);
  border-bottom: 1px solid var(--border-color);
  backdrop-filter: blur(12px);
  position: sticky;
  top: 0;
  z-index: 50;
}

.nav-container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 var(--space-4);
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 4rem;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  font-size: var(--text-xl);
  font-weight: var(--font-bold);
  color: var(--text-primary);
  text-decoration: none;
}

.nav-menu {
  display: flex;
  align-items: center;
  gap: var(--space-3);
}

.nav-btn {
  background: transparent;
  border: none;
  padding: var(--space-2);
  border-radius: 0.375rem;
  color: var(--text-secondary);
  transition: all var(--duration-fast) var(--ease-out);
  cursor: pointer;
  position: relative;
}

.nav-btn:hover {
  color: var(--fractal-primary);
  transform: rotate(15deg) scale(1.1);
}

.cart-container {
  position: relative;
}

.cart-badge {
  position: absolute;
  top: -10px;
  right: -10px;
  background: var(--error);
  color: white;
  border-radius: 50%;
  min-width: 20px;
  height: 20px;
  font-size: var(--text-xs);
  font-weight: var(--font-bold);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}
```

### Organisms

#### Checkout Progress
```css
.checkout-progress {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: var(--space-8);
  padding: var(--space-6) 0;
}

.progress-step {
  display: flex;
  align-items: center;
  gap: var(--space-3);
  position: relative;
}

.progress-step:not(:last-child)::after {
  content: '';
  width: 3rem;
  height: 2px;
  background: var(--gray-200);
  position: absolute;
  top: 50%;
  right: -2.25rem;
  transform: translateY(-50%);
  z-index: -1;
}

.progress-step.completed::after {
  background: var(--success-green);
}

.progress-circle {
  width: var(--space-8);
  height: var(--space-8);
  border-radius: 50%;
  background: var(--gray-200);
  color: var(--gray-500);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--text-sm);
  font-weight: var(--font-bold);
  transition: all var(--duration-normal) var(--ease-out);
}

.progress-step.active .progress-circle {
  background: var(--fractal-primary);
  color: white;
  transform: scale(1.1);
}

.progress-step.completed .progress-circle {
  background: var(--success-green);
  color: white;
}

.progress-label {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--text-secondary);
}

.progress-step.active .progress-label {
  color: var(--fractal-primary);
  font-weight: var(--font-semibold);
}
```

## 🎭 Brand Voice & Tone (Aarron Walter)

### Brand Personality
```markdown
**FractalMerch Personality:**
- **Creativo**: Innovador en personalización, piensa fuera de la caja
- **Confiable**: Seguro en transacciones, cumple promesas
- **Accesible**: Fácil de usar, lenguaje claro y directo
- **Entusiasta**: Apasionado por el diseño, energético pero no agresivo
- **Profesional**: Competente en e-commerce, orientado a resultados
```

### Tone by Context
```markdown
**Página Principal**: Entusiasta y creativo
- "¡Diseña la remera de tus sueños!"
- "Donde tu creatividad cobra vida"

**Producto/Tienda**: Informativo y confiable  
- "Remera Premium 100% Algodón"
- "Envío gratis en compras superiores a $10.000"

**Checkout**: Serio y confiable
- "Información de envío segura"
- "Tu pedido está protegido"

**Error/Problemas**: Empático y solucionador
- "Ups, algo salió mal. Te ayudamos a solucionarlo"
- "Nuestro equipo ya está trabajando en esto"

**Éxito/Confirmación**: Celebratorio pero profesional
- "¡Pedido confirmado! Tu diseño está en camino"
- "¡Listo! Te enviamos los detalles por email"
```

### Microcopies Emocionales
```markdown
**Loading States**:
- "Preparando tu diseño..." (en lugar de "Cargando...")
- "Calculando el mejor precio..." (en lugar de "Procesando...")

**Empty States**:
- "Tu carrito está esperando diseños increíbles" (en lugar de "Carrito vacío")
- "Aún no hay favoritos, ¿qué tal si empezamos?" (en lugar de "Sin elementos")

**Call to Actions**:
- "Crear mi diseño" (en lugar de "Personalizar")
- "Llevar a casa" (en lugar de "Comprar")
- "Hacer realidad" (en lugar de "Finalizar")

**Trust Signals**:
- "100% Seguro" con ícono de escudo
- "Envío Garantizado" con ícono de camión
- "Soporte 24/7" con ícono de chat
```

## 📱 Responsive Strategy (Mobile-First)

### Grid System
```css
.container {
  width: 100%;
  margin: 0 auto;
  padding: 0 var(--space-4);
}

/* Mobile First - 320px+ */
.grid {
  display: grid;
  gap: var(--space-4);
  grid-template-columns: 1fr;
}

/* Small devices - 640px+ */
@media (min-width: 640px) {
  .container {
    max-width: 640px;
  }
  
  .grid-sm-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Medium devices - 768px+ */
@media (min-width: 768px) {
  .container {
    max-width: 768px;
    padding: 0 var(--space-6);
  }
  
  .grid-md-3 {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Large devices - 1024px+ */
@media (min-width: 1024px) {
  .container {
    max-width: 1024px;
  }
  
  .grid-lg-4 {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Extra large devices - 1280px+ */
@media (min-width: 1280px) {
  .container {
    max-width: 1280px;
  }
}
```

## ⚡ Performance Guidelines

### Critical CSS
```css
/* Above-the-fold critical styles */
.hero-section,
.nav-header,
.product-grid {
  /* Inline in <head> for first paint */
}
```

### Lazy Loading
```css
.lazy-image {
  opacity: 0;
  transition: opacity var(--duration-normal) var(--ease-out);
}

.lazy-image.loaded {
  opacity: 1;
}
```

### Prefers Reduced Motion
```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

**Última actualización:** 2025-07-04
**Versión:** 1.0 - Sistema Base Premium
**Mantenedor:** Claude Assistant