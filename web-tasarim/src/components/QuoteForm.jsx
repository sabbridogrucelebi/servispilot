import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Send, MapPin, Users, Calendar } from 'lucide-react';

export default function QuoteForm() {
  const [isSubmitted, setIsSubmitted] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    setIsSubmitted(true);
    setTimeout(() => setIsSubmitted(false), 5000);
  };

  return (
    <section id="teklif" className="section" style={{ position: 'relative' }}>
      <div style={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', width: '100vw', height: '100%', background: 'radial-gradient(ellipse at center, rgba(16,84,156,0.05) 0%, rgba(255,255,255,1) 80%)', zIndex: -1 }}></div>

      <div style={{ maxWidth: '800px', margin: '0 auto', position: 'relative', zIndex: 10 }}>
        <motion.div 
          className="glass-panel"
          style={{ padding: '60px 40px', textAlign: 'center' }}
          initial={{ opacity: 0, scale: 0.95 }}
          whileInView={{ opacity: 1, scale: 1 }}
          viewport={{ once: true }}
          transition={{ duration: 0.5 }}
        >
          <h2 className="section-title" style={{ fontSize: '2.5rem' }}>Anında Teklif Alın</h2>
          <p style={{ color: 'var(--color-text-secondary)', marginBottom: '40px' }}>
            Rota ve yolcu detaylarınızı paylaşın, yapay zeka destekli sistemimiz size en uygun araç ve fiyat bilgisini hazırlasın.
          </p>

          <AnimatePresence mode="wait">
            {!isSubmitted ? (
              <motion.form 
                key="form"
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0, y: -20 }}
                onSubmit={handleSubmit}
                style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '24px', textAlign: 'left' }}
              >
                <div style={{ gridColumn: '1 / -1' }}>
                  <label className="label"><MapPin size={14} style={{ display: 'inline', marginRight: '4px' }}/> Hizmet Türü</label>
                  <select className="input-field" required style={{ appearance: 'none' }}>
                    <option value="">Seçiniz...</option>
                    <option value="personel">Personel Taşımacılığı</option>
                    <option value="ogrenci">Öğrenci Servisi</option>
                    <option value="organizasyon">Organizasyon / Gezi</option>
                  </select>
                </div>
                
                <div>
                  <label className="label">Kalkış Noktası</label>
                  <input type="text" className="input-field" placeholder="Örn: Kadıköy" required />
                </div>
                
                <div>
                  <label className="label">Varış Noktası</label>
                  <input type="text" className="input-field" placeholder="Örn: Levent" required />
                </div>
                
                <div>
                  <label className="label"><Users size={14} style={{ display: 'inline', marginRight: '4px' }}/> Yolcu Sayısı</label>
                  <input type="number" min="1" className="input-field" placeholder="Kişi Sayısı" required />
                </div>
                
                <div>
                  <label className="label"><Calendar size={14} style={{ display: 'inline', marginRight: '4px' }}/> Tarih</label>
                  <input type="date" className="input-field" required />
                </div>
                
                <div style={{ gridColumn: '1 / -1', marginTop: '16px' }}>
                  <motion.button 
                    type="submit" 
                    className="btn-primary" 
                    style={{ width: '100%', padding: '16px' }}
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                  >
                    Fiyat Teklifi İste <Send size={18} />
                  </motion.button>
                </div>
              </motion.form>
            ) : (
              <motion.div 
                key="success"
                initial={{ opacity: 0, scale: 0.8 }}
                animate={{ opacity: 1, scale: 1 }}
                style={{ padding: '40px 0', color: '#4ade80' }}
              >
                <div style={{ fontSize: '4rem', marginBottom: '16px' }}>✓</div>
                <h3 style={{ fontSize: '1.5rem', color: 'var(--color-text-primary)' }}>Talebiniz Alındı!</h3>
                <p style={{ color: 'var(--color-text-secondary)', marginTop: '8px' }}>Operasyon ekibimiz en kısa sürede sizinle iletişime geçecektir.</p>
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>
      </div>
    </section>
  );
}
