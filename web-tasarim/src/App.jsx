import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bus, Menu, X } from 'lucide-react';
import Hero from './components/Hero';
import Services from './components/Services';
import Fleet from './components/Fleet';
import QuoteForm from './components/QuoteForm';

function App() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setScrolled(window.scrollY > 50);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <div style={{ position: 'relative' }}>
      {/* Navbar */}
      <motion.nav
        style={{
          position: 'fixed',
          top: 0,
          left: 0,
          right: 0,
          zIndex: 100,
          padding: '15px 5%',
          background: '#FFFFFF',
          borderBottom: '1px solid rgba(0,0,0,0.05)',
          boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          height: '90px',
        }}
        initial={{ y: -100 }}
        animate={{ y: 0 }}
        transition={{ duration: 0.6 }}
      >
        {/* Logo Container */}
        <div style={{ display: 'flex', alignItems: 'center', height: '100%' }}>
          {/* Kullanıcı logoyu public/images/logo.png yoluna yükleyecek */}
          <img 
            src="/images/logo.png" 
            alt="Mehmet Çelebi Turizm" 
            style={{ 
              height: '60px', 
              maxHeight: '60px', 
              objectFit: 'contain' 
            }} 
            onError={(e) => {
              // Logo bulunamazsa (kullanıcı henüz yüklemediyse) geçici metin göster
              e.target.style.display = 'none';
              e.target.nextSibling.style.display = 'flex';
            }}
          />
          <span style={{ display: 'none', flexDirection: 'column', lineHeight: 1, color: 'var(--color-accent)', fontWeight: 800, fontSize: '1.5rem' }}>
            <span>Mehmet Çelebi</span>
            <span style={{ fontWeight: 300, fontSize: '0.8rem', color: 'var(--color-accent-secondary)' }}>Turizm</span>
          </span>
        </div>

        {/* Desktop Menu */}
        <div className="desktop-menu" style={{ display: 'flex', gap: '32px', alignItems: 'center' }}>
          {['Hizmetler', 'Filo', 'Hakkımızda', 'İletişim'].map((item) => (
            <a key={item} href={`#${item.toLowerCase()}`} style={{ fontSize: '1rem', fontWeight: 600, color: 'var(--color-text-primary)', transition: 'color 0.3s ease' }} onMouseOver={(e) => e.target.style.color = 'var(--color-accent)'} onMouseOut={(e) => e.target.style.color = 'var(--color-text-primary)'}>
              {item}
            </a>
          ))}
          <a href="#teklif" className="btn-primary" style={{ padding: '10px 20px', fontSize: '0.875rem' }}>
            Teklif Al
          </a>
        </div>

        {/* Mobile Toggle */}
        <button className="mobile-toggle" style={{ display: 'none', background: 'none', border: 'none', color: 'var(--color-text-primary)' }} onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
          {mobileMenuOpen ? <X size={28} /> : <Menu size={28} />}
        </button>
      </motion.nav>

      {/* Main Content */}
      <main style={{ paddingTop: '90px' }}>
        <Hero />
        <Services />
        <Fleet />
        <QuoteForm />
      </main>

      {/* Footer */}
      <footer style={{ borderTop: '1px solid var(--color-border)', padding: '60px 5% 40px', background: 'var(--color-bg-secondary)', marginTop: '60px' }}>
        <div style={{ maxWidth: '1200px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '40px', marginBottom: '40px' }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '12px', fontSize: '1.25rem', fontWeight: 800, fontFamily: 'var(--font-heading)', marginBottom: '20px' }}>
              <Bus size={20} className="gradient-text" />
              <span>Mehmet Çelebi Turizm</span>
            </div>
            <p style={{ color: 'var(--color-text-secondary)', fontSize: '0.875rem', lineHeight: 1.6 }}>
              Teknoloji destekli yeni nesil taşımacılık çözümleri. Lüks, güven ve konfor bir arada.
            </p>
          </div>
          <div>
            <h4 style={{ marginBottom: '20px' }}>Hizmetler</h4>
            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <li><a href="#" style={{ color: 'var(--color-text-secondary)', fontSize: '0.875rem' }}>Personel Taşıma</a></li>
              <li><a href="#" style={{ color: 'var(--color-text-secondary)', fontSize: '0.875rem' }}>Öğrenci Servisi</a></li>
              <li><a href="#" style={{ color: 'var(--color-text-secondary)', fontSize: '0.875rem' }}>VIP Organizasyon</a></li>
            </ul>
          </div>
          <div>
            <h4 style={{ marginBottom: '20px' }}>İletişim</h4>
            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '12px' }}>
              <li style={{ color: 'var(--color-text-secondary)', fontSize: '0.875rem' }}>info@mehmetcelebiturizm.com</li>
              <li style={{ color: 'var(--color-text-secondary)', fontSize: '0.875rem' }}>+90 (850) 123 45 67</li>
            </ul>
          </div>
        </div>
        <div style={{ textAlign: 'center', color: 'var(--color-text-secondary)', fontSize: '0.75rem', borderTop: '1px solid rgba(0,0,0,0.05)', paddingTop: '24px' }}>
          © 2026 Mehmet Çelebi Turizm. Tüm hakları saklıdır.
        </div>
      </footer>

      <style dangerouslySetInnerHTML={{__html: `
        @media (max-width: 768px) {
          .desktop-menu { display: none !important; }
          .mobile-toggle { display: block !important; }
        }
      `}} />
    </div>
  );
}

export default App;
