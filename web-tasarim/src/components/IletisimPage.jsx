import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Phone, MapPin, Send, Clock, CheckCircle2 } from 'lucide-react';

export default function IletisimPage() {
  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsSubmitted(true);
    setTimeout(() => setIsSubmitted(false), 5000);
  };

  return (
    <motion.div 
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      transition={{ duration: 0.5 }}
      style={{ 
        display: 'flex', 
        flexWrap: 'wrap', 
        minHeight: '100vh', 
        width: '100%', 
        paddingTop: 0
      }}
    >
      {/* Left Panel - Blue Full Height */}
      <div style={{
        flex: '1 1 50%',
        minWidth: '300px',
        background: 'linear-gradient(135deg, #0a192f 0%, #10549c 100%)',
        color: 'white',
        padding: '160px 10% 80px',
        position: 'relative',
        overflow: 'hidden',
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center'
      }}>
        {/* Decorative BG */}
        <div style={{ position: 'absolute', top: '-50px', right: '-50px', width: '300px', height: '300px', background: 'rgba(255,255,255,0.05)', borderRadius: '50%' }}></div>
        <div style={{ position: 'absolute', bottom: '-50px', left: '-50px', width: '300px', height: '300px', background: 'rgba(226,27,27,0.15)', borderRadius: '50%' }}></div>
        
        <div style={{ position: 'relative', zIndex: 10, maxWidth: '600px', margin: '0 auto', width: '100%' }}>
          <h2 style={{ fontSize: '3rem', fontWeight: 800, marginBottom: '20px', fontFamily: 'var(--font-heading)' }}>İletişime Geçin</h2>
          <p style={{ fontSize: '1.2rem', color: 'rgba(255,255,255,0.8)', marginBottom: '60px', lineHeight: 1.6 }}>
            Size en uygun fiyat teklifini sunabilmemiz ve sorularınızı yanıtlayabilmemiz için bizimle iletişime geçin.
          </p>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '40px' }}>
            <div style={{ display: 'flex', gap: '24px', alignItems: 'flex-start' }}>
              <div style={{ background: 'rgba(255,255,255,0.1)', padding: '16px', borderRadius: '50%', color: 'var(--color-accent-secondary)' }}>
                <Phone size={28} />
              </div>
              <div>
                <h4 style={{ fontSize: '1.1rem', marginBottom: '8px', fontWeight: 600, color: 'rgba(255,255,255,0.7)', textTransform: 'uppercase', letterSpacing: '1px' }}>Telefon</h4>
                <p style={{ fontSize: '1.5rem', fontWeight: 700, letterSpacing: '1px' }}>+90 532 473 35 64</p>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '24px', alignItems: 'flex-start' }}>
              <div style={{ background: 'rgba(255,255,255,0.1)', padding: '16px', borderRadius: '50%', color: 'var(--color-accent-secondary)' }}>
                <MapPin size={28} />
              </div>
              <div>
                <h4 style={{ fontSize: '1.1rem', marginBottom: '8px', fontWeight: 600, color: 'rgba(255,255,255,0.7)', textTransform: 'uppercase', letterSpacing: '1px' }}>Adres</h4>
                <p style={{ fontSize: '1.1rem', color: 'rgba(255,255,255,0.9)', lineHeight: 1.6 }}>
                  Fevziçakmak Mahallesi<br/>
                  10591. Sk No:26/A<br/>
                  Karatay - KONYA
                </p>
              </div>
            </div>

            <div style={{ display: 'flex', gap: '24px', alignItems: 'flex-start' }}>
              <div style={{ background: 'rgba(255,255,255,0.1)', padding: '16px', borderRadius: '50%', color: 'var(--color-accent-secondary)' }}>
                <Clock size={28} />
              </div>
              <div>
                <h4 style={{ fontSize: '1.1rem', marginBottom: '8px', fontWeight: 600, color: 'rgba(255,255,255,0.7)', textTransform: 'uppercase', letterSpacing: '1px' }}>Çalışma Saatleri</h4>
                <p style={{ fontSize: '1.1rem', color: 'rgba(255,255,255,0.9)' }}>7 Gün / 24 Saat Hizmetinizdeyiz</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Right Panel - Form Full Height */}
      <div style={{ 
        flex: '1 1 50%', 
        minWidth: '300px',
        padding: '160px 10% 80px', 
        background: '#f8fafc',
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center'
      }}>
        <div style={{ maxWidth: '600px', margin: '0 auto', width: '100%' }}>
          <h3 style={{ fontSize: '2.5rem', marginBottom: '40px', color: 'var(--color-text-primary)', fontFamily: 'var(--font-heading)' }}>
            Hemen Teklif Alın
          </h3>
          
          <AnimatePresence mode="wait">
            {!isSubmitted ? (
              <motion.form 
                key="form"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0, scale: 0.95 }}
                onSubmit={handleSubmit}
                style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '24px' }}
              >
                <div style={{ gridColumn: '1 / -1' }}>
                  <label className="label">Adınız Soyadınız / Kurum Adı</label>
                  <input type="text" className="input-field" placeholder="İsminiz" required style={{ background: 'white' }} />
                </div>
                
                <div>
                  <label className="label">Telefon Numarası</label>
                  <input type="tel" className="input-field" placeholder="05XX XXX XX XX" required style={{ background: 'white' }} />
                </div>
                
                <div>
                  <label className="label">Hizmet Türü</label>
                  <select className="input-field" required style={{ background: 'white', appearance: 'none' }}>
                    <option value="">Seçiniz...</option>
                    <option value="personel">Personel Taşımacılığı</option>
                    <option value="ogrenci">Öğrenci Servis Taşımacılığı</option>
                    <option value="turizm">Turizm ve Gezi Taşımacılığı</option>
                    <option value="vip">VIP Transfer</option>
                    <option value="kiralama">Araç Kiralama</option>
                  </select>
                </div>
                
                <div style={{ gridColumn: '1 / -1' }}>
                  <label className="label">Mesajınız / Talebiniz</label>
                  <textarea 
                    className="input-field" 
                    rows="4" 
                    placeholder="Güzergah, kişi sayısı veya özel taleplerinizi belirtebilirsiniz..." 
                    style={{ background: 'white', resize: 'vertical' }}
                  ></textarea>
                </div>
                
                <div style={{ gridColumn: '1 / -1', marginTop: '10px' }}>
                  <button 
                    type="submit" 
                    className="btn-3d-premium" 
                    style={{ 
                      width: '100%', 
                      padding: '16px', 
                      fontSize: '1.2rem', 
                      borderRadius: '12px',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      gap: '12px'
                    }}
                  >
                    <span>Gönder</span> <Send size={20} style={{ position: 'relative', zIndex: 2 }} />
                  </button>
                </div>
              </motion.form>
            ) : (
              <motion.div 
                key="success"
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                style={{ 
                  height: '100%', display: 'flex', flexDirection: 'column', 
                  alignItems: 'center', justifyContent: 'center', textAlign: 'center',
                  color: 'var(--color-text-primary)',
                  padding: '60px 0'
                }}
              >
                <motion.div
                  initial={{ scale: 0 }}
                  animate={{ scale: 1 }}
                  transition={{ type: "spring", stiffness: 200, damping: 15 }}
                >
                  <CheckCircle2 size={100} color="#10b981" style={{ marginBottom: '24px' }} />
                </motion.div>
                <h3 style={{ fontSize: '2.5rem', marginBottom: '16px', fontFamily: 'var(--font-heading)' }}>Talebiniz Alındı!</h3>
                <p style={{ fontSize: '1.2rem', color: 'var(--color-text-secondary)', lineHeight: 1.6 }}>
                  Müşteri temsilcilerimiz en kısa sürede sizinle iletişime geçerek detaylı fiyat teklifimizi sunacaktır.
                </p>
              </motion.div>
            )}
          </AnimatePresence>
        </div>
      </div>
    </motion.div>
  );
}
