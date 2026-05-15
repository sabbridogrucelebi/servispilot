import React from 'react';
import { motion } from 'framer-motion';
import { Users, GraduationCap, Map } from 'lucide-react';

const services = [
  {
    id: 1,
    title: "Personel Taşımacılığı",
    description: "Kurumsal şirketler için verimli, zamanında ve prestijli ulaşım çözümleri. İş gücünüzü konforla taşıyor, şirketinizin dinamiğine güç katıyoruz.",
    icon: <Users size={40} className="gradient-text" />,
    delay: 0.1
  },
  {
    id: 2,
    title: "Öğrenci Servisi",
    description: "Geleceğimizin teminatı öğrencilerimiz için maksimum güvenlik. Veli bilgilendirme sistemi ve özel eğitimli sürücülerle huzur dolu yolculuklar.",
    icon: <GraduationCap size={40} className="gradient-text" />,
    delay: 0.3
  },
  {
    id: 3,
    title: "Organizasyon & Gezi",
    description: "Özel günler, turistik turlar ve VIP etkinlikler için dinamik filo yapımızla özel taşımacılık deneyimi. Rotanızı belirleyin, gerisini bize bırakın.",
    icon: <Map size={40} className="gradient-text" />,
    delay: 0.5
  }
];

export default function Services() {
  return (
    <section id="hizmetler" className="section">
      <div className="bg-glow-2"></div>
      
      <div style={{ maxWidth: '1200px', margin: '0 auto', position: 'relative', zIndex: 10 }}>
        <motion.h2 
          className="section-title"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6 }}
        >
          Hizmetlerimiz
        </motion.h2>
        <motion.p 
          className="section-subtitle"
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ duration: 0.6, delay: 0.1 }}
        >
          Her yolculuğun amacına özel tasarlanmış, teknolojiyle entegre modern ulaşım çözümlerimizle tanışın.
        </motion.p>
        
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(300px, 1fr))', gap: '32px', marginTop: '64px' }}>
          {services.map((service) => (
            <motion.div
              key={service.id}
              className="glass-panel"
              style={{ padding: '40px', position: 'relative', overflow: 'hidden' }}
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ duration: 0.6, delay: service.delay }}
              whileHover={{ y: -10, transition: { duration: 0.3 } }}
            >
              {/* Decorative accent */}
              <div style={{ position: 'absolute', top: 0, left: 0, width: '100%', height: '4px', background: 'linear-gradient(90deg, var(--color-accent) 0%, var(--color-accent-secondary) 100%)' }}></div>
              
              <div style={{ marginBottom: '24px', display: 'inline-block', padding: '16px', background: 'rgba(0,0,0,0.03)', borderRadius: '16px', boxShadow: 'inset 0 0 20px rgba(0,0,0,0.02)' }}>
                {service.icon}
              </div>
              
              <h3 style={{ fontSize: '1.5rem', marginBottom: '16px' }}>{service.title}</h3>
              <p style={{ color: 'var(--color-text-secondary)', lineHeight: 1.6, fontSize: '1rem' }}>
                {service.description}
              </p>
              
              <motion.button 
                style={{ background: 'none', border: 'none', color: 'var(--color-text-primary)', marginTop: '24px', cursor: 'pointer', display: 'flex', alignItems: 'center', gap: '8px', fontSize: '0.875rem', fontWeight: 600, fontFamily: 'var(--font-heading)' }}
                whileHover={{ x: 5, color: 'var(--color-accent)' }}
              >
                Daha Fazla İncele <span style={{ color: 'var(--color-accent)' }}>→</span>
              </motion.button>
            </motion.div>
          ))}
        </div>
      </div>
    </section>
  );
}
