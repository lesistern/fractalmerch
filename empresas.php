<?php
require_once 'includes/functions.php';

$page_title = 'Soluciones Empresariales';
include 'includes/header.php';
?>

<section class="business-hero">
    <div class="container">
        <div class="hero-content-business">
            <h1>Soluciones Empresariales de Sublimación</h1>
            <p>Equipos industriales, capacitación especializada y soporte técnico para hacer crecer tu negocio</p>
            <div class="business-stats">
                <div class="stat">
                    <h3>500+</h3>
                    <p>Empresas confían en nosotros</p>
                </div>
                <div class="stat">
                    <h3>24/7</h3>
                    <p>Soporte técnico</p>
                </div>
                <div class="stat">
                    <h3>15</h3>
                    <p>Años de experiencia</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="business-solutions">
    <div class="container">
        <h2>Nuestras Soluciones</h2>
        <div class="solutions-grid">
            <div class="solution-card">
                <i class="fas fa-industry"></i>
                <h3>Equipos Industriales</h3>
                <p>Impresoras de gran formato, prensas térmicas industriales y hornos de sublimación de alta capacidad.</p>
                <ul>
                    <li>Producción continua 24/7</li>
                    <li>Capacidad hasta 1000 piezas/día</li>
                    <li>Garantía extendida 3 años</li>
                </ul>
                <a href="#" class="btn btn-primary">Ver Equipos</a>
            </div>
            
            <div class="solution-card">
                <i class="fas fa-graduation-cap"></i>
                <h3>Capacitación Técnica</h3>
                <p>Programas de entrenamiento para tu equipo con certificación internacional.</p>
                <ul>
                    <li>Cursos presenciales y virtuales</li>
                    <li>Certificación internacional</li>
                    <li>Material didáctico incluido</li>
                </ul>
                <a href="#" class="btn btn-primary">Ver Cursos</a>
            </div>
            
            <div class="solution-card">
                <i class="fas fa-tools"></i>
                <h3>Soporte Técnico</h3>
                <p>Mantenimiento preventivo, reparaciones y asistencia técnica especializada.</p>
                <ul>
                    <li>Técnicos certificados</li>
                    <li>Respuesta en menos de 4 horas</li>
                    <li>Repuestos originales garantizados</li>
                </ul>
                <a href="#" class="btn btn-primary">Contactar Soporte</a>
            </div>
            
            <div class="solution-card">
                <i class="fas fa-chart-line"></i>
                <h3>Consultoría de Negocio</h3>
                <p>Asesoramiento estratégico para optimizar tu operación y maximizar rentabilidad.</p>
                <ul>
                    <li>Análisis de rentabilidad</li>
                    <li>Optimización de procesos</li>
                    <li>Estrategias de mercado</li>
                </ul>
                <a href="#" class="btn btn-primary">Solicitar Consulta</a>
            </div>
        </div>
    </div>
</section>

<section class="business-clients">
    <div class="container">
        <h2>Empresas que Confían en Nosotros</h2>
        <div class="clients-logos">
            <div class="client-logo">Cliente 1</div>
            <div class="client-logo">Cliente 2</div>
            <div class="client-logo">Cliente 3</div>
            <div class="client-logo">Cliente 4</div>
            <div class="client-logo">Cliente 5</div>
            <div class="client-logo">Cliente 6</div>
        </div>
    </div>
</section>

<section class="business-contact">
    <div class="container">
        <div class="contact-content">
            <div class="contact-info">
                <h2>¿Listo para hacer crecer tu negocio?</h2>
                <p>Nuestro equipo de especialistas está listo para asesorarte y diseñar la solución perfecta para tu empresa.</p>
                <div class="contact-methods">
                    <div class="contact-method">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Llamanos</h4>
                            <p>+54 11 1234-5678</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email</h4>
                            <p>empresas@sublime.com</p>
                        </div>
                    </div>
                    <div class="contact-method">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <h4>Agenda una reunión</h4>
                            <p>Disponible de Lun-Vie 9:00-18:00</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="contact-form">
                <h3>Solicita una Cotización</h3>
                <form>
                    <div class="form-group">
                        <input type="text" placeholder="Nombre de la empresa" required>
                    </div>
                    <div class="form-group">
                        <input type="text" placeholder="Nombre del contacto" required>
                    </div>
                    <div class="form-group">
                        <input type="email" placeholder="Email corporativo" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" placeholder="Teléfono" required>
                    </div>
                    <div class="form-group">
                        <select required>
                            <option value="">Tipo de solución</option>
                            <option value="equipos">Equipos Industriales</option>
                            <option value="capacitacion">Capacitación</option>
                            <option value="soporte">Soporte Técnico</option>
                            <option value="consultoria">Consultoría</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea placeholder="Cuéntanos sobre tu proyecto" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Enviar Solicitud</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>