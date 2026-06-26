export interface Candidate {
  number: string;
  name: string;
  school: string;
  dni: string;
  code: string;
}

export interface FacultyCouncil {
  facultyName: string;
  candidates: Candidate[];
}

export interface PartyData {
  id: string;
  name: string;
  listNumber: string;
  motto: string;
  proposalContent: string;
  asamblea: Candidate[];
  consejoUni: Candidate[];
  consejoFac: FacultyCouncil[];
}

export const partiesData: Record<string, PartyData> = {
  'fuerza': {
    id: 'fuerza',
    name: 'FUERZA UPT',
    listNumber: '02',
    motto: '"La Evolución"',
    proposalContent: 'Fuerza UPT es una agrupación estudiantil comprometida con la excelencia académica, la defensa de los derechos de los universitarios y la constante innovación en nuestra casa de estudios. Nuestro equipo está conformado por líderes de diversas facultades unidos bajo el propósito común de impulsar a la Universidad Privada de Tacna hacia una verdadera evolución estructural y académica.\n\nNuestras principales propuestas son:\n1. Examen de Recuperación Estandarizado: Aprobación de un reglamento transparente para instaurar un examen sustitutorio o de recuperación al finalizar cada semestre académico, brindando una segunda oportunidad justa a todos los estudiantes.\n2. Fondo Concursable de Investigación: Creación de un programa de financiamiento directo para proyectos de tesis, artículos científicos y trabajos de investigación de estudiantes de pregrado, incentivando la producción intelectual.\n3. Convenios y Prácticas Preprofesionales: Expansión agresiva y firma de nuevos convenios institucionales para asegurar oportunidades laborales y de prácticas con las empresas e instituciones más importantes de la macrorregión sur.\n4. Capacitación Constante: Implementación de talleres gratuitos y certificaciones con valor oficial en herramientas tecnológicas demandadas y enseñanza de idiomas.',
    asamblea: [
      { number: '01', name: 'QUIROZ RAMOS, ANALYN ROSARIO', school: 'Escuela Profesional de Ingeniería Civil', dni: '60802453', code: '2024079764' },
      { number: '02', name: 'QUIHUE PACO, XIMENA ALEXANDRA', school: 'Escuela Profesional de Derecho', dni: '71136043', code: '2022073439' },
      { number: '03', name: 'LINARES MAMANI, ARACELY YASHMI', school: 'Escuela Profesional de Ingeniería Comercial', dni: '60834918', code: '2024079958' },
      { number: '04', name: 'CAIRO FLORES, GABRIELA ALESSANDRA', school: 'Psicología', dni: '60989209', code: '2024079826' },
      { number: '05', name: 'HUAYHUA ZAPANA, SOL CRISTINA', school: 'Carrera Profesional de Terapia Física y Rehabilitación', dni: '75890848', code: '2023077017' },
      { number: '06', name: 'TICONA MAMANI, CESAR EDDU', school: 'Escuela Profesional de Derecho', dni: '75248749', code: '2023077424' },
      { number: '07', name: 'COTRADO LOPEZ, DELIA ELENA', school: 'Escuela Profesional de Ciencias Contables y Financieras', dni: '75545643', code: '2025000559' },
      { number: '08', name: 'FANO CHOQUE, DYANEIRA ELIZT', school: 'Escuela Profesional de Arquitectura', dni: '60802396', code: '2024081321' }
    ],
    consejoUni: [
      { number: '01', name: 'ACERO ILLACHURA, ELIAS OMAR', school: 'Escuela Profesional de Ingeniería Civil', dni: '60989386', code: '2024081329' },
      { number: '02', name: 'OLOYA MONTES, ABRIL ESMERALDA', school: 'Escuela Profesional de Ingeniería Comercial', dni: '76785773', code: '2025000720' },
      { number: '03', name: 'CARDENAS MEZA, ANDREA TATIANA', school: 'Psicología', dni: '71134222', code: '2021069857' },
      { number: '04', name: 'ROSADO PEREZ, DOMENIKA MIKAELA', school: 'Escuela Profesional de Arquitectura', dni: '60744503', code: '2024081586' },
      { number: '05', name: 'MONTESINOS QUISPE, TATIANA ANTUANE', school: 'Escuela Profesional de Arquitectura', dni: '60444285', code: '2024079886' }
    ],
    consejoFac: [
      {
        facultyName: 'FAING',
        candidates: [
          { number: '01', name: 'CORDERO LOPEZ, NOE MAYCOL', school: 'Escuela Profesional de Ingeniería Civil', dni: '60204511', code: '2024081280' },
          { number: '02', name: 'MAMANI SARDON, JOSHTIN MARTIN', school: 'Escuela Profesional de Ingeniería Civil', dni: '74449292', code: '2024081500' }
        ]
      },
      {
        facultyName: 'FAEDCOH',
        candidates: [
          { number: '01', name: 'LAQUI QUISPE, HAMMY MARGARET', school: 'Psicología', dni: '78426159', code: '2024080008' },
          { number: '02', name: 'MARCA MAMANI, YARI ELIZABETH', school: 'Educación Inicial', dni: '70693551', code: '2022075669' },
          { number: '03', name: 'MALAGA ZUÑIGA, ROLANDO MAURICIO', school: 'Psicología', dni: '72567965', code: '2022075766' }
        ]
      },
      {
        facultyName: 'FADE',
        candidates: [
          { number: '01', name: 'ROMERO FLORES, FABIAN', school: 'Escuela Profesional de Derecho', dni: '60802370', code: '2024081303' },
          { number: '02', name: 'BACA TALA, ISAAC NELSON', school: 'Escuela Profesional de Derecho', dni: '71458593', code: '2023077188' },
          { number: '03', name: 'TORRES CHALLCO, RUSY NICOL', school: 'Escuela Profesional de Derecho', dni: '60169904', code: '2022073848' }
        ]
      },
      {
        facultyName: 'FACSA',
        candidates: []
      },
      {
        facultyName: 'FACEM',
        candidates: [
          { number: '00', name: 'PEREZ CANAZA, JHOSELIN JHOSARA', school: 'Escuela Profesional de Ciencias Contables y Financieras', dni: '70656556', code: '2022075557' },
          { number: '01', name: 'CHAMBILLA VARGAS, Yenny Luz Estefani', school: 'Escuela Profesional de Ingeniería Comercial', dni: '76760973', code: '2020069041' },
          { number: '02', name: 'CONDORI LUQUE, LUCERO ANDREA MIA', school: 'Escuela Profesional de Ingeniería Comercial', dni: '75064207', code: '2024079626' },
          { number: '03', name: 'CASANA LUNA, YUZAF MATEO', school: 'Escuela Profesional de Ingeniería Comercial', dni: '60899915', code: '2024079625' }
        ]
      },
      {
        facultyName: 'FAU',
        candidates: [
          { number: '00', name: 'QUISOCALA TICONA, YESSICA CARMEN', school: 'Escuela Profesional de Arquitectura', dni: '60959069', code: '2024080046' },
          { number: '01', name: 'MENDOZA HERRERA, YOSELYM ALEXANDRA', school: 'Escuela Profesional de Arquitectura', dni: '61076219', code: '2024079895' },
          { number: '02', name: 'ZAMATA RIVERA, ANDREA KATERINE', school: 'Escuela Profesional de Arquitectura', dni: '60989239', code: '2024079563' }
        ]
      }
    ]
  },
  'reu': {
    id: 'reu',
    name: 'REU UPT',
    listNumber: '03',
    motto: '"Renovación Universitaria"',
    proposalContent: 'Renovación Universitaria (REU UPT) se consolida como la organización estudiantil líder en promover el bienestar universitario, el emprendimiento joven y una vida universitaria integral. Buscamos empoderar a los estudiantes para que no solo destaquen en las aulas, sino que cuenten con las herramientas necesarias para el éxito profesional, apoyados siempre por una gestión empática y cercana a sus necesidades.\n\nNuestras principales propuestas son:\n1. EmprendeFest-UPT y Capital Semilla: Creación de un evento semestral de gran magnitud acompañado de un fondo de inversión para financiar, asesorar e impulsar los negocios y startups creadas por los mismos estudiantes.\n2. Bienestar y Salud Mental: Fortalecimiento del departamento médico y psicológico, garantizando el acceso a especialistas, talleres de manejo del estrés y acompañamiento gratuito para preservar la salud emocional del alumnado.\n3. Integración y Voluntariado: Organización de las "Olimpiadas Interfacultades UPT" y fomento de redes de voluntariado social (como nuestro programa "REUniendo Patitas") para fortalecer la identidad universitaria.\n4. Inserción Laboral Inmediata: Aumento del presupuesto destinado a la bolsa de trabajo y ferias laborales al finalizar cada semestre, conectando directamente a nuestros egresados con empresas de alto perfil.',
    asamblea: [
      { number: '01', name: 'SALAS JIMENEZ, Walter Emmanuel', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '61373633', code: '2022073896' },
      { number: '02', name: 'PEÑA PAREDES, AMARIS MERCEDES', school: 'Escuela Profesional de Economía', dni: '61057430', code: '2024016613' },
      { number: '03', name: 'MONTESINOS PEREZ, MACARENA ARIANA', school: 'Psicología', dni: '70575415', code: '2022075512' },
      { number: '04', name: 'BELLIDO ARANA, Pedro Luis', school: 'Carrera Profesional de Terapia Física y Rehabilitación', dni: '42031590', code: '2024079842' },
      { number: '05', name: 'TICONA RAMIREZ, VANESA ARACELI', school: 'Escuela Profesional de Arquitectura', dni: '60450644', code: '2025000964' },
      { number: '06', name: 'RAMOS INCACOÑA, YOSELYN MIRELLA', school: 'Escuela Profesional de Derecho', dni: '71061861', code: '2022073454' },
      { number: '07', name: 'LOAYZA RODRIGUEZ, OSKAR DANIEL', school: 'Escuela Profesional de Ciencias Contables y Financieras', dni: '70748141', code: '2023077051' },
      { number: '08', name: 'PEREZ VILLANUEVA, NADHYRA ISABEL', school: 'Carrera Profesional de Terapia Física y Rehabilitación', dni: '71141557', code: '2025001304' },
      { number: '09', name: 'OSCO ROJAS, LORENA DEL PILAR', school: 'Escuela Profesional de Arquitectura', dni: '71492125', code: '2025001336' },
      { number: '10', name: 'YUPA GOMEZ, FATIMA SOFIA', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '71255434', code: '2023076618' },
      { number: '11', name: 'MAQUERA CHINO, ALISON ANAHI', school: 'Escuela Profesional de Derecho', dni: '60899635', code: '2024081507' },
      { number: '12', name: 'SARMIENTO ESPINOZA, FERNANDA VALERY', school: 'Escuela Profesional de Economía', dni: '61928405', code: '2023076586' }
    ],
    consejoUni: [
      { number: '01', name: 'JIMENEZ VALER, JHAJAIRA MAYTE', school: 'Escuela Profesional de Odontología', dni: '71476821', code: '2024016675' },
      { number: '02', name: 'TORRICO CONCHA, TRIANA VALENTINA', school: 'Escuela Profesional de Arquitectura', dni: '60064772', code: '2024080141' },
      { number: '03', name: 'VELARDE ACOSTA, MICHELLE RUBI', school: 'Psicología', dni: '60802253', code: '2024080181' },
      { number: '04', name: 'TELLO SORIA, ANA CLAUDIA', school: 'Escuela Profesional de Derecho', dni: '71447101', code: '2023078248' },
      { number: '05', name: 'OLIVERA GOMEZ, SOFIA ALEJANDRA', school: 'Administración de Negocios Internacionales', dni: '60268340', code: '2024079853' }
    ],
    consejoFac: [
      {
        facultyName: 'FAING',
        candidates: [
          { number: '01', name: 'CHURA ENCINAS, LUIS EDGARDO', school: 'Escuela Profesional de Ingeniería Civil', dni: '71215390', code: '2024079621' },
          { number: '02', name: 'GOMEZ MUCHO, JOSEPH ALBERT ERNESTO', school: 'Escuela Profesional de Ingeniería Electrónica', dni: '60899473', code: '2024081433' },
          { number: '03', name: 'VALENZA COPA, GABRIELA INDIRA', school: 'Escuela Profesional de Ingeniería Civil', dni: '60532211', code: '2024079941' },
          { number: '04', name: 'GONZALEZ SOSA, LUCIANA', school: 'Escuela Profesional de Ingeniería Industrial', dni: '70788742', code: '2023077095' }
        ]
      },
      {
        facultyName: 'FAEDCOH',
        candidates: [
          { number: '01', name: 'FLORES MANINI, OLENKA ARACELY', school: 'Psicología', dni: '60899708', code: '2024016594' },
          { number: '02', name: 'DELGADO BERRIOS, THIANY VALERIA', school: 'Escuela Profesional de Ciencias de la Comunicación', dni: '71025813', code: '2024079712' },
          { number: '03', name: 'ALAVE REGENTE, JESUS EDUARDO', school: 'Escuela Profesional de Ciencias de la Comunicación', dni: '72787596', code: '2023076497' }
        ]
      },
      {
        facultyName: 'FADE',
        candidates: [
          { number: '01', name: 'MERA ZEA, SEBÁSTHIAN LUCIANO', school: 'Escuela Profesional de Derecho', dni: '71172730', code: '2025000614' },
          { number: '02', name: 'MAMANI ANTIPUERTAS, SEBASTIAN GONZALO', school: 'Escuela Profesional de Derecho', dni: '71345540', code: '2022073449' }
        ]
      },
      {
        facultyName: 'FACSA',
        candidates: [
          { number: '01', name: 'FUENTES SHAPIAMA, DAYANNA MARGIORYE', school: 'Carrera Profesional de Terapia Física y Rehabilitación', dni: '72726390', code: '2024079663' },
          { number: '02', name: 'ROMERO JIMENEZ, DENIS CHRISTIAN', school: 'Escuela Profesional de Odontología', dni: '76769820', code: '2024079810' },
          { number: '03', name: 'ACO ZEVALLOS, DAYANA KIAMARA', school: 'Carrera Profesional de Laboratorio Clínico y Anatomía Patológica', dni: '61188138', code: '2024079986' },
          { number: '04', name: 'PILCO CONDORI, ANAYENSI GALILEA', school: 'Carrera Profesional de Laboratorio Clínico y Anatomía Patológica', dni: '71248941', code: '2023077125' }
        ]
      },
      {
        facultyName: 'FACEM',
        candidates: [
          { number: '01', name: 'ALARCON DILL-ERVA, DHAYRA FERNANDA', school: 'Administración de Negocios Internacionales', dni: '71157644', code: '2025000254' },
          { number: '02', name: 'VELASQUEZ GIRÓN, MARÍA FERNANDA', school: 'Administración de Negocios Internacionales', dni: '60833000', code: '2024079666' }
        ]
      },
      {
        facultyName: 'FAU',
        candidates: [
          { number: '01', name: 'TINTAYA CHACHAQUE, JOEL EDSON', school: 'Escuela Profesional de Arquitectura', dni: '61010569', code: '2025001335' },
          { number: '02', name: 'MAMANI CONDORI, MARYCIELO BRANDI', school: 'Escuela Profesional de Arquitectura', dni: '61060121', code: '2025001331' }
        ]
      }
    ]
  },
  'feut': {
    id: 'feut',
    name: 'FEUT UPT',
    listNumber: '01',
    motto: '"Sangre Nueva"',
    proposalContent: 'FEUT UPT nace como una alternativa fresca, transparente y orientada a resultados tangibles. Nos enfocamos en fiscalizar los recursos, modernizar la infraestructura de todas las facultades y garantizar que los servicios básicos de la universidad estén a la altura de lo que los estudiantes merecen. Somos la voz de aquellos que buscan un cambio administrativo profundo.\n\nNuestras principales propuestas son:\n1. Servicio de Transporte y Buses Académicos: Implementación de nuevas rutas estratégicas, mayor disponibilidad de buses y ampliación de los horarios de servicio para asegurar la movilidad segura y eficiente de toda la comunidad universitaria.\n2. Modernización de Cafeterías: Renovación total de los espacios de comedor y estricta fiscalización concesionaria para garantizar menús estudiantiles que sean tanto económicos como altamente nutritivos.\n3. Infraestructura y Coworking: Actualización de equipos en todos los laboratorios de cómputo y habilitación de nuevos espacios de coworking equipados con internet de alta velocidad y enchufes para el trabajo colaborativo.\n4. Universidad Cero Papel: Digitalización completa de todos los trámites documentarios. Promovemos una plataforma moderna donde matrículas, solicitudes y procesos de titulación se realicen 100% online y sin demoras.',
    asamblea: [
      { number: '01', name: 'NECIOSUP VARGAS, ARIANA LUCERO', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '60899845', code: '2024080126' },
      { number: '02', name: 'ROMERO ASCACIO, SAMUEL JOSE', school: 'Escuela Profesional de Derecho', dni: '003255452', code: '2025001357' },
      { number: '03', name: 'FERIA ESTRADA, ELISA DENISS', school: 'Escuela Profesional de Ingeniería Comercial', dni: '70984526', code: '2022075722' },
      { number: '04', name: 'VILCA AROCUTIPA, INGRID MARIZU', school: 'Escuela Profesional de Ciencias de la Comunicación', dni: '60989122', code: '2024081615' },
      { number: '05', name: 'VALVERDE CATACORA, FABIOLA LUCIANA', school: 'Escuela Profesional de Medicina Humana', dni: '72867738', code: '2023077558' },
      { number: '06', name: 'FALCONI VEGA, FRANSHESCA YERUSSA', school: 'Escuela Profesional de Arquitectura', dni: '60801972', code: '2023077371' },
      { number: '07', name: 'CHOQUE JACCO, FABRIZZIO ALFREDO', school: 'Escuela Profesional de Ingeniería Industrial', dni: '60802290', code: '2024081376' },
      { number: '08', name: 'LOMBARDI LLANOS, MARIA JOSE ANTONELLA', school: 'Escuela Profesional de Derecho', dni: '71142181', code: '2022075600' },
      { number: '09', name: 'LARICO MAMANI, TREICY JHADIRA', school: 'Escuela Profesional de Ingeniería Comercial', dni: '60170156', code: '2023077359' },
      { number: '10', name: 'SANCHEZ DELGADO, OMAR ADAN', school: 'Psicología', dni: '77807158', code: '2024081325' },
      { number: '11', name: 'GAMBETTA VASQUEZ, ANTONELLA MICHELLE', school: 'Escuela Profesional de Arquitectura', dni: '71321383', code: '2023077373' },
      { number: '12', name: 'GARCIA CHAMBILLA, CRISTOPHER LUCIANO URIEL', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '70873254', code: '2024082112' },
      { number: '13', name: 'COPA COLQUE, ASLHY NORA', school: 'Escuela Profesional de Derecho', dni: '61038075', code: '2021072368' },
      { number: '14', name: 'RAA RODRIGUEZ, MICAELA PATRICIA', school: 'Escuela Profesional de Ingeniería Comercial', dni: '60464143', code: '2024081195' }
    ],
    consejoUni: [
      { number: '01', name: 'RAMIREZ GUTIERREZ, ANDREA DEL ROSARIO', school: 'Escuela Profesional de Derecho', dni: '71602289', code: '2023078246' },
      { number: '02', name: 'LOZA ILLACHURA, KIYOMI ANGELA', school: 'Escuela Profesional de Ingeniería Comercial', dni: '74148309', code: '2024081254' },
      { number: '03', name: 'ATENCIO VARGAS, EMANUEL FELIPE JOSE', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '60802344', code: '2024079964' },
      { number: '04', name: 'SYED KARIM GIL, NOOR HANEEFA', school: 'Carrera Profesional de Laboratorio Clínico y Anatomía Patológica', dni: '60989343', code: '2025001206' }
    ],
    consejoFac: [
      {
        facultyName: 'FAING',
        candidates: [
          { number: '01', name: 'LUPACA CALANI, MATTIAS ALESSANDRO', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '60457048', code: '2024016553' },
          { number: '02', name: 'HARO SANDOVAL, LUCERO D ANGELA', school: 'Escuela Profesional de Ingeniería de Sistemas', dni: '60170189', code: '2024081439' }
        ]
      },
      {
        facultyName: 'FAEDCOH',
        candidates: [
          { number: '01', name: 'TORRES CACERES, SANDRO LUIS ROMULO', school: 'Educación Física y Deportes', dni: '71253308', code: '2025000459' },
          { number: '02', name: 'GAMARRA PALUMBO, STEFANO VICTOR ADRIANO', school: 'Escuela Profesional de Ciencias de la Comunicación', dni: '61310405', code: '2025000293' }
        ]
      },
      {
        facultyName: 'FADE',
        candidates: [
          { number: '01', name: 'PAUCAR RUFFRAN, PAOLA LUCIA', school: 'Escuela Profesional de Derecho', dni: '61038514', code: '2022073453' },
          { number: '02', name: 'FLORES HUAMAN, SUGAR DOMINIC', school: 'Escuela Profesional de Derecho', dni: '77919998', code: '2023078556' },
          { number: '03', name: 'SANCHEZ LOMAS, CIELO LUNA', school: 'Escuela Profesional de Derecho', dni: '60959091', code: '2023078747' },
          { number: '04', name: 'KURIS TICONA, MARIA FERNANDA', school: 'Escuela Profesional de Derecho', dni: '72904547', code: '2023078271' }
        ]
      },
      {
        facultyName: 'FACSA',
        candidates: [
          { number: '01', name: 'HIROYASU DAVILA, CESAR SERGIO JESUS', school: 'Escuela Profesional de Medicina Humana', dni: '70831816', code: '2024016634' },
          { number: '02', name: 'MAQUERA SILVA, ARIANA MILAGROS', school: 'Carrera Profesional de Laboratorio Clínico y Anatomía Patológica', dni: '61187633', code: '2025000876' },
          { number: '03', name: 'DIAZ CONDORI, KAMILA LOURDES', school: 'Escuela Profesional de Odontología', dni: '60735986', code: '2024079866' },
          { number: '04', name: 'ROSADO MAMANI, DENARD ADRIAN', school: 'Escuela Profesional de Medicina Humana', dni: '73860810', code: '2023076904' }
        ]
      },
      {
        facultyName: 'FACEM',
        candidates: [
          { number: '01', name: 'JARRO PONGO, ANALI MARIELA', school: 'Escuela Profesional de Ciencias Contables y Financieras', dni: '72110028', code: '2024081459' },
          { number: '02', name: 'AGUILAR MAMANI, ABIHAIL DINA', school: 'Escuela Profesional de Ingeniería Comercial', dni: '75808765', code: '2022075725' }
        ]
      },
      {
        facultyName: 'FAU',
        candidates: [
          { number: '01', name: 'HUAMANCHA LLAMOCCA, SOL ARIDYA', school: 'Escuela Profesional de Arquitectura', dni: '60049275', code: '2024080043' },
          { number: '02', name: 'PEREIRA YAPUCHURA, ADRIANA VICTORIA', school: 'Escuela Profesional de Arquitectura', dni: '61373907', code: '2025001334' },
          { number: '03', name: 'GUTIERREZ VELARDE, CRISTEL CELESTE', school: 'Escuela Profesional de Arquitectura', dni: '62750644', code: '2025000538' },
          { number: '04', name: 'PERCCA FLORES, LUIS ENRIQUE DANIEL', school: 'Escuela Profesional de Arquitectura', dni: '74322838', code: '2023077037' }
        ]
      }
    ]
  }
};
