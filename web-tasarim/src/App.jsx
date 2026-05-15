import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Bus, Menu, X, Phone, Mail, MapPin } from 'lucide-react';
import Hero from './components/Hero';
import QuickServices from './components/QuickServices';
import Services from './components/Services';
import AracFilomuz from './components/AracFilomuz';
import QuoteForm from './components/QuoteForm';
import OgrenciTasimaciligi from './components/OgrenciTasimaciligi';
import PersonelTasimaciligi from './components/PersonelTasimaciligi';
import TurizmTasimaciligi from './components/TurizmTasimaciligi';
import VipTransfer from './components/VipTransfer';
import AracKiralama from './components/AracKiralama';
import IletisimPage from './components/IletisimPage';
import WhatsAppButton from './components/WhatsAppButton';
import FiloDetay from './components/FiloDetay';

function App() {
  const [scrolled, setScrolled] = useState(false);
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
  const [activeServiceIndex, setActiveServiceIndex] = useState(0);
  const [currentPage, setCurrentPage] = useState('home');
  const [selectedFleetCategory, setSelectedFleetCategory] = useState('otobus');

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
          padding: '0 5%',
          background: '#FFFFFF',
          borderBottom: '1px solid rgba(0,0,0,0.05)',
          boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
          display: 'grid',
          gridTemplateColumns: 'auto 1fr auto',
          alignItems: 'center',
          height: '100px',
          overflow: 'visible'
        }}
        initial={{ y: -100 }}
        animate={{ y: 0 }}
        transition={{ duration: 0.6 }}
      >
        {/* Logo Container */}
        <div 
          onClick={() => {
            setCurrentPage('home');
            window.scrollTo(0, 0);
          }}
          style={{ position: 'relative', height: '100px', width: '300px', display: 'flex', alignItems: 'center', overflow: 'visible', cursor: 'pointer' }}
        >
          <img 
            src="/images/logo.png" 
            alt="Mehmet Çelebi Turizm" 
            style={{ 
              position: 'absolute',
              top: '-35px', /* Görselin üstündeki şeffaf boşluğu yoksaymak için yukarı çekildi */
              left: '0',
              height: '170px', /* Logo daha da büyütüldü */
              width: 'auto',
              maxWidth: '400px', 
              objectFit: 'contain',
              objectPosition: 'left top', 
              zIndex: 9999, 
              filter: 'drop-shadow(0 6px 12px rgba(0,0,0,0.2))'
            }} 
            onError={(e) => {
              e.target.style.display = 'none';
              e.target.nextSibling.style.display = 'flex';
            }}
          />
          <span style={{ display: 'none', flexDirection: 'column', lineHeight: 1, color: 'var(--color-accent)', fontWeight: 800, fontSize: '1.5rem' }}>
            <span>Mehmet Çelebi</span>
            <span style={{ fontWeight: 300, fontSize: '0.8rem', color: 'var(--color-accent-secondary)' }}>Turizm</span>
          </span>
        </div>

        {/* Desktop Menu - Ortalanmış */}
        <div className="desktop-menu" style={{ display: 'flex', justifyContent: 'center', gap: '32px' }}>
          {[
            { id: 'anasayfa', label: 'ANASAYFA' },
            { id: 'hakkimizda', label: 'HAKKIMIZDA' },
            { id: 'hizmetlerimiz', label: 'HİZMETLERİMİZ' },
            { id: 'filomuz', label: 'FİLOMUZ' },
            { id: 'medya', label: 'MEDYA' },
            { id: 'iletisim', label: 'BİZE ULAŞIN' }
          ].map((item) => (
            <a 
              key={item.id} 
              href={`#${item.id}`} 
              className="nav-link"
              onClick={(e) => {
                if (item.id === 'anasayfa') {
                  e.preventDefault();
                  setCurrentPage('home');
                  window.scrollTo(0, 0);
                } else if (currentPage !== 'home') {
                  // If on another page, go home first, then scroll
                  e.preventDefault();
                  setCurrentPage('home');
                  setTimeout(() => {
                    document.getElementById(item.id)?.scrollIntoView({ behavior: 'smooth' });
                  }, 100);
                }
              }}
            >
              {item.label}
            </a>
          ))}
        </div>
        
        {/* Giriş Yap Butonu - Sağa Dayalı */}
        <div className="desktop-menu" style={{ display: 'flex', justifyContent: 'flex-end', alignItems: 'center' }}>
          <motion.a 
            href="https://mehmetcelebiturizm.com/app/login" 
            whileHover={{ scale: 1.05 }}
            style={{ 
              textDecoration: 'none',
              display: 'flex',
              alignItems: 'center',
              gap: '8px',
            }}
          >
            <span style={{ 
              display: 'inline-block', 
              transform: 'scaleY(1.3)', 
              fontWeight: 900, 
              color: '#e21b1b',
              fontSize: '1.6rem',
              marginTop: '-2px'
            }}>
              //
            </span>
            <motion.span 
              animate={{ backgroundPosition: ['200% center', '0% center'] }}
              transition={{ repeat: Infinity, duration: 3, ease: "linear" }}
              style={{
                fontSize: '1.35rem',
                fontWeight: 800,
                fontFamily: 'var(--font-heading)',
                background: 'linear-gradient(to right, #10549c 20%, #e21b1b 50%, #10549c 80%)',
                backgroundSize: '200% auto',
                WebkitBackgroundClip: 'text',
                WebkitTextFillColor: 'transparent',
                display: 'inline-block',
                letterSpacing: '1px'
              }}
            >
              GİRİŞ YAP
            </motion.span>
          </motion.a>
        </div>

        {/* Mobile Toggle */}
        <button className="mobile-toggle" style={{ display: 'none', background: 'none', border: 'none', color: 'var(--color-text-primary)' }} onClick={() => setMobileMenuOpen(!mobileMenuOpen)}>
          {mobileMenuOpen ? <X size={28} /> : <Menu size={28} />}
        </button>
      </motion.nav>

      {/* Main Content Area */}
      <AnimatePresence mode="wait">
        {currentPage === 'home' ? (
          <motion.main 
            key="home"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            style={{ paddingTop: '100px' }}
          >
            <Hero 
              activeIndex={activeServiceIndex} 
              onVideoEnd={() => setActiveServiceIndex(prev => (prev + 1) % 5)} 
              onCTAClick={(index) => {
                if (index === 0) { // Personel Taşımacılığı
                  setCurrentPage('personel');
                  window.scrollTo(0, 0);
                } else if (index === 1) { // Öğrenci Taşımacılığı
                  setCurrentPage('ogrenci');
                  window.scrollTo(0, 0);
                } else if (index === 2) { // Turizm Taşımacılığı
                  setCurrentPage('turizm');
                  window.scrollTo(0, 0);
                } else if (index === 3) { // VIP Transfer
                  setCurrentPage('vip');
                  window.scrollTo(0, 0);
                } else if (index === 4) { // Araç Kiralama
                  setCurrentPage('kiralama');
                  window.scrollTo(0, 0);
                } else {
                  // Diğer hizmetler için şimdilik form'a kaydır
                  document.getElementById('iletisim')?.scrollIntoView({ behavior: 'smooth' });
                }
              }}
            />
            <QuickServices 
              activeIndex={activeServiceIndex} 
              onServiceClick={(idx) => {
                setActiveServiceIndex(idx);
                if (idx === 0) {
                  setCurrentPage('personel');
                  window.scrollTo(0, 0);
                } else if (idx === 1) {
                  setCurrentPage('ogrenci');
                  window.scrollTo(0, 0);
                } else if (idx === 2) {
                  setCurrentPage('turizm');
                  window.scrollTo(0, 0);
                } else if (idx === 3) {
                  setCurrentPage('vip');
                  window.scrollTo(0, 0);
                } else if (idx === 4) {
                  setCurrentPage('kiralama');
                  window.scrollTo(0, 0);
                }
              }} 
            />
            <AracFilomuz 
            onVehicleClick={(id) => {
              setSelectedFleetCategory(id);
              setCurrentPage('filo-detay');
              window.scrollTo(0, 0);
            }}
          />
            <Services />
            <QuoteForm />
          </motion.main>
        ) : currentPage === 'personel' ? (
          <motion.div key="personel" style={{ paddingTop: '100px' }}>
            <PersonelTasimaciligi 
              onBack={() => {
                setCurrentPage('home');
                window.scrollTo(0, 0);
              }} 
              onQuoteClick={() => {
                setCurrentPage('iletisim');
                window.scrollTo(0, 0);
              }}
            />
          </motion.div>
        ) : currentPage === 'ogrenci' ? (
          <motion.div key="ogrenci" style={{ paddingTop: '100px' }}>
            <OgrenciTasimaciligi 
              onBack={() => {
                setCurrentPage('home');
                window.scrollTo(0, 0);
              }} 
              onQuoteClick={() => {
                setCurrentPage('iletisim');
                window.scrollTo(0, 0);
              }}
            />
          </motion.div>
        ) : currentPage === 'turizm' ? (
          <motion.div key="turizm" style={{ paddingTop: '100px' }}>
            <TurizmTasimaciligi 
              onBack={() => { setCurrentPage('home'); window.scrollTo(0, 0); }} 
              onQuoteClick={() => { setCurrentPage('iletisim'); window.scrollTo(0, 0); }}
            />
          </motion.div>
        ) : currentPage === 'vip' ? (
          <motion.div key="vip" style={{ paddingTop: '100px' }}>
            <VipTransfer 
              onBack={() => { setCurrentPage('home'); window.scrollTo(0, 0); }} 
              onQuoteClick={() => { setCurrentPage('iletisim'); window.scrollTo(0, 0); }}
            />
          </motion.div>
        ) : currentPage === 'kiralama' ? (
          <motion.div key="kiralama" style={{ paddingTop: '100px' }}>
            <AracKiralama 
              onBack={() => { setCurrentPage('home'); window.scrollTo(0, 0); }} 
              onQuoteClick={() => { setCurrentPage('iletisim'); window.scrollTo(0, 0); }}
            />
          </motion.div>
        ) : currentPage === 'filo-detay' ? (
          <motion.div key="filo-detay" style={{ paddingTop: '100px' }}>
            <FiloDetay 
              category={selectedFleetCategory}
              onBack={() => { setCurrentPage('home'); window.scrollTo(0, 0); }} 
              onQuoteClick={() => { setCurrentPage('iletisim'); window.scrollTo(0, 0); }}
            />
          </motion.div>
        ) : currentPage === 'iletisim' ? (
          <motion.div key="iletisim" style={{ paddingTop: '100px' }}>
            <IletisimPage onBack={() => {
              setCurrentPage('home');
              window.scrollTo(0, 0);
            }} />
          </motion.div>
        ) : null}
      </AnimatePresence>

      {/* Premium Footer */}
      <footer style={{ 
        background: 'linear-gradient(135deg, #0a192f 0%, #072e5a 100%)', 
        color: 'white',
        padding: '80px 5% 40px',
        position: 'relative',
        overflow: 'hidden'
      }}>
        {/* Decorative elements */}
        <div style={{ position: 'absolute', top: 0, right: '10%', width: '300px', height: '300px', background: 'radial-gradient(circle, rgba(226,27,27,0.15) 0%, transparent 70%)', borderRadius: '50%' }}></div>
        <div style={{ position: 'absolute', bottom: '-50px', left: '-50px', width: '200px', height: '200px', background: 'radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%)', borderRadius: '50%' }}></div>

        <div style={{ maxWidth: '1200px', margin: '0 auto', display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))', gap: '50px', marginBottom: '60px', position: 'relative', zIndex: 10 }}>
          
          {/* Brand Col */}
          <div>
            <div style={{ marginBottom: '25px', display: 'inline-flex', flexDirection: 'column', alignItems: 'center' }}>
              <motion.h2 
                animate={{ backgroundPosition: ['200% center', '0% center'] }}
                transition={{ repeat: Infinity, duration: 4, ease: "linear" }}
                style={{ 
                  fontSize: '2.2rem', 
                  fontWeight: 900, 
                  fontFamily: 'var(--font-heading)', 
                  margin: 0,
                  background: 'linear-gradient(to right, #ffffff 20%, #e21b1b 50%, #ffffff 80%)',
                  backgroundSize: '200% auto',
                  WebkitBackgroundClip: 'text',
                  WebkitTextFillColor: 'transparent',
                  display: 'block',
                  letterSpacing: '-1px'
                }}>
                MEHMET ÇELEBİ
              </motion.h2>
              <h3 style={{ 
                fontSize: '1.3rem', 
                fontWeight: 600, 
                letterSpacing: '8px', 
                color: 'rgba(255,255,255,0.8)', 
                margin: 0, 
                marginTop: '4px',
                marginLeft: '8px'
              }}>
                TURİZM
              </h3>
            </div>
            <p style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', lineHeight: 1.8 }}>
              Teknoloji destekli yeni nesil taşımacılık çözümleri. Lüks, güven ve konforu yılların tecrübesiyle birleştiriyoruz.
            </p>
          </div>

          {/* Links Col */}
          <div>
            <h4 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '25px', letterSpacing: '1px' }}>HIZLI BAĞLANTILAR</h4>
            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '15px', padding: 0, margin: 0 }}>
              <li><a href="#hizmetlerimiz" style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', textDecoration: 'none', transition: 'color 0.3s' }}>Personel Taşımacılığı</a></li>
              <li><a href="#hizmetlerimiz" style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', textDecoration: 'none', transition: 'color 0.3s' }}>Öğrenci Servis Taşımacılığı</a></li>
              <li><a href="#hizmetlerimiz" style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', textDecoration: 'none', transition: 'color 0.3s' }}>VIP ve Turizm Taşımacılığı</a></li>
              <li><a href="#filomuz" style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', textDecoration: 'none', transition: 'color 0.3s' }}>Geniş Filomuz</a></li>
            </ul>
          </div>

          {/* Contact Col */}
          <div>
            <h4 style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '25px', letterSpacing: '1px' }}>İLETİŞİM</h4>
            <ul style={{ listStyle: 'none', display: 'flex', flexDirection: 'column', gap: '20px', padding: 0, margin: 0 }}>
              <li style={{ display: 'flex', gap: '15px', alignItems: 'flex-start' }}>
                <Phone size={20} color="var(--color-accent-secondary)" style={{ marginTop: '2px' }} />
                <span style={{ color: 'rgba(255,255,255,0.9)', fontSize: '1rem', fontWeight: 600 }}>+90 532 473 35 64</span>
              </li>
              <li style={{ display: 'flex', gap: '15px', alignItems: 'flex-start' }}>
                <Mail size={20} color="var(--color-accent-secondary)" style={{ marginTop: '2px' }} />
                <span style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem' }}>info@mehmetcelebiturizm.com</span>
              </li>
              <li style={{ display: 'flex', gap: '15px', alignItems: 'flex-start' }}>
                <MapPin size={24} color="var(--color-accent-secondary)" style={{ marginTop: '2px', flexShrink: 0 }} />
                <span style={{ color: 'rgba(255,255,255,0.7)', fontSize: '1rem', lineHeight: 1.6 }}>Fevziçakmak Mah. 10591. Sk No:26/A<br/>Karatay - KONYA</span>
              </li>
            </ul>
          </div>
        </div>

        <div style={{ 
          maxWidth: '1200px', margin: '0 auto', textAlign: 'center', 
          color: 'rgba(255,255,255,0.5)', fontSize: '0.9rem', 
          borderTop: '1px solid rgba(255,255,255,0.1)', paddingTop: '30px',
          position: 'relative', zIndex: 10
        }}>
          © 2026 Mehmet Çelebi Turizm. Tüm hakları saklıdır.
        </div>
      </footer>

      {/* Global WhatsApp Button */}
      <WhatsAppButton />

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
