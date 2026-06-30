-- ============================================================
-- SEEDERS DE DEMOSTRACIÓN - IntegrUPT
-- ============================================================

-- ============================================================
-- CURSOS ADICIONALES
-- ============================================================
LOCK TABLES `cursos` WRITE;
/*!40000 ALTER TABLE `cursos` DISABLE KEYS */;
INSERT INTO `cursos` VALUES
(3,'Ingeniería de Software I',1,2,'7',1),
(4,'Base de Datos I',1,2,'6',1),
(5,'Estructuras de Datos',1,2,'5',1),
(6,'Algoritmos y Programación',1,2,'4',1),
(7,'Redes de Computadoras',1,2,'8',1),
(8,'Inteligencia Artificial',1,2,'9',1),
(9,'Sistemas Operativos',1,2,'6',1),
(10,'Diseño Web y Aplicaciones',1,2,'5',1),
(11,'Mecánica de Materiales',1,1,'2',1),
(12,'Resistencia de Materiales',1,1,'4',1),
(13,'Concreto Armado',1,1,'6',1),
(14,'Hidráulica General',1,1,'7',1);
/*!40000 ALTER TABLE `cursos` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- SÍLABOS - 4 DOCENTES, 3-4 CURSOS CADA UNO
-- Docente 1: JUAN ALBERTO RODRIGUEZ PAZ     → juarodr@upt.pe
-- Docente 2: ISRAEL NAZARETH CHAPARRO CRUZ  → isrchap@upt.pe
-- Docente 3: LUIS ENRIQUE MARTINEZ CRUZ     → luimart@upt.pe
-- Docente 4: CARLOS PATRICIO PEREZ LOZANO   → carpere@upt.pe
-- Contraseña de todos: 123456
-- ============================================================
LOCK TABLES `silabo` WRITE;
/*!40000 ALTER TABLE `silabo` DISABLE KEYS */;
INSERT INTO `silabo` VALUES
-- Docente 1: JUAN ALBERTO RODRIGUEZ PAZ (juarodr@upt.pe)
(2,'IS-701','Ingeniería de Software I',7,'2026-I',4,4,'JUAN ALBERTO RODRIGUEZ PAZ',NULL,2,NULL,NULL,1,'2026-06-30 08:00:00','2026-06-30 08:00:00'),
(3,'BD-601','Base de Datos I',6,'2026-I',4,4,'JUAN ALBERTO RODRIGUEZ PAZ',NULL,2,NULL,NULL,1,'2026-06-30 08:05:00','2026-06-30 08:05:00'),
(4,'AD-601','Análisis y Diseño de Sistemas',6,'2026-I',3,3,'JUAN ALBERTO RODRIGUEZ PAZ',NULL,2,NULL,NULL,1,'2026-06-30 08:10:00','2026-06-30 08:10:00'),
-- Docente 2: ISRAEL NAZARETH CHAPARRO CRUZ (isrchap@upt.pe)
(5,'ED-501','Estructuras de Datos',5,'2026-I',4,4,'ISRAEL NAZARETH CHAPARRO CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:15:00','2026-06-30 08:15:00'),
(6,'AL-401','Algoritmos y Programación',4,'2026-I',3,3,'ISRAEL NAZARETH CHAPARRO CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:20:00','2026-06-30 08:20:00'),
(7,'IA-901','Inteligencia Artificial',9,'2026-I',3,3,'ISRAEL NAZARETH CHAPARRO CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:25:00','2026-06-30 08:25:00'),
(8,'SO-601','Sistemas Operativos',6,'2026-I',4,4,'ISRAEL NAZARETH CHAPARRO CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:30:00','2026-06-30 08:30:00'),
-- Docente 3: LUIS ENRIQUE MARTINEZ CRUZ (luimart@upt.pe)
(9,'RC-801','Redes de Computadoras',8,'2026-I',4,4,'LUIS ENRIQUE MARTINEZ CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:35:00','2026-06-30 08:35:00'),
(10,'DW-501','Diseño Web y Aplicaciones',5,'2026-I',3,3,'LUIS ENRIQUE MARTINEZ CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:40:00','2026-06-30 08:40:00'),
(11,'BD-602','Base de Datos II',7,'2026-I',4,4,'LUIS ENRIQUE MARTINEZ CRUZ',NULL,2,NULL,NULL,1,'2026-06-30 08:45:00','2026-06-30 08:45:00'),
-- Docente 4: CARLOS PATRICIO PEREZ LOZANO (carpere@upt.pe)
(12,'CI-211','Mecánica de Materiales',2,'2026-I',4,4,'CARLOS PATRICIO PEREZ LOZANO',NULL,2,NULL,NULL,1,'2026-06-30 08:50:00','2026-06-30 08:50:00'),
(13,'CI-411','Resistencia de Materiales',4,'2026-I',4,4,'CARLOS PATRICIO PEREZ LOZANO',NULL,2,NULL,NULL,1,'2026-06-30 08:55:00','2026-06-30 08:55:00'),
(14,'CI-611','Concreto Armado',6,'2026-I',4,4,'CARLOS PATRICIO PEREZ LOZANO',NULL,2,NULL,NULL,1,'2026-06-30 09:00:00','2026-06-30 09:00:00'),
(15,'CI-711','Hidráulica General',7,'2026-I',3,3,'CARLOS PATRICIO PEREZ LOZANO',NULL,2,NULL,NULL,1,'2026-06-30 09:05:00','2026-06-30 09:05:00');
/*!40000 ALTER TABLE `silabo` ENABLE KEYS */;
UNLOCK TABLES;

-- Unidades de sílabos
LOCK TABLES `silabo_unidad` WRITE;
/*!40000 ALTER TABLE `silabo_unidad` DISABLE KEYS */;
INSERT INTO `silabo_unidad` VALUES
(2,2,1,'Fundamentos de la Ingeniería de Software',16,'2026-06-30 08:00:00','2026-06-30 08:00:00'),
(3,2,2,'Metodologías de Desarrollo',16,'2026-06-30 08:00:00','2026-06-30 08:00:00'),
(4,3,1,'Modelo Relacional y SQL Básico',16,'2026-06-30 08:05:00','2026-06-30 08:05:00'),
(5,3,2,'Consultas Avanzadas y Normalización',16,'2026-06-30 08:05:00','2026-06-30 08:05:00'),
(6,4,1,'Levantamiento de Requerimientos',12,'2026-06-30 08:10:00','2026-06-30 08:10:00'),
(7,4,2,'Modelado UML',12,'2026-06-30 08:10:00','2026-06-30 08:10:00'),
(8,5,1,'Arrays, Listas y Pilas',16,'2026-06-30 08:15:00','2026-06-30 08:15:00'),
(9,5,2,'Árboles y Grafos',16,'2026-06-30 08:15:00','2026-06-30 08:15:00'),
(10,6,1,'Introducción a la Algoritmia',12,'2026-06-30 08:20:00','2026-06-30 08:20:00'),
(11,6,2,'Recursividad y Ordenamiento',12,'2026-06-30 08:20:00','2026-06-30 08:20:00'),
(12,7,1,'Fundamentos de IA y Búsqueda',12,'2026-06-30 08:25:00','2026-06-30 08:25:00'),
(13,7,2,'Aprendizaje Automático',12,'2026-06-30 08:25:00','2026-06-30 08:25:00'),
(14,8,1,'Procesos e Hilos',16,'2026-06-30 08:30:00','2026-06-30 08:30:00'),
(15,8,2,'Memoria Virtual y Sistemas de Archivos',16,'2026-06-30 08:30:00','2026-06-30 08:30:00'),
(16,9,1,'Modelo OSI y Protocolos TCP/IP',16,'2026-06-30 08:35:00','2026-06-30 08:35:00'),
(17,9,2,'Seguridad en Redes',16,'2026-06-30 08:35:00','2026-06-30 08:35:00'),
(18,10,1,'HTML5 y CSS3',12,'2026-06-30 08:40:00','2026-06-30 08:40:00'),
(19,10,2,'JavaScript y Frameworks Frontend',12,'2026-06-30 08:40:00','2026-06-30 08:40:00'),
(20,11,1,'Consultas SQL Avanzadas',16,'2026-06-30 08:45:00','2026-06-30 08:45:00'),
(21,11,2,'Procedimientos Almacenados y Triggers',16,'2026-06-30 08:45:00','2026-06-30 08:45:00'),
(22,12,1,'Propiedades Mecánicas de los Materiales',16,'2026-06-30 08:50:00','2026-06-30 08:50:00'),
(23,12,2,'Esfuerzo y Deformación',16,'2026-06-30 08:50:00','2026-06-30 08:50:00'),
(24,13,1,'Tensión, Compresión y Flexión',16,'2026-06-30 08:55:00','2026-06-30 08:55:00'),
(25,13,2,'Torsión y Pandeo',16,'2026-06-30 08:55:00','2026-06-30 08:55:00'),
(26,14,1,'Diseño de Vigas y Columnas',16,'2026-06-30 09:00:00','2026-06-30 09:00:00'),
(27,14,2,'Diseño de Losas y Zapatas',16,'2026-06-30 09:00:00','2026-06-30 09:00:00'),
(28,15,1,'Hidrostática y Dinámica de Fluidos',12,'2026-06-30 09:05:00','2026-06-30 09:05:00'),
(29,15,2,'Flujo en Tuberías y Canales',12,'2026-06-30 09:05:00','2026-06-30 09:05:00');
/*!40000 ALTER TABLE `silabo_unidad` ENABLE KEYS */;
UNLOCK TABLES;

-- Temas de sílabos
LOCK TABLES `silabo_tema` WRITE;
/*!40000 ALTER TABLE `silabo_tema` DISABLE KEYS */;
INSERT INTO `silabo_tema` VALUES
(3,2,1,'Definición y alcance de la Ingeniería de Software.','Revisión de estándares internacionales (IEEE 12207).',0,'2026-06-30 08:00:00','2026-06-30 08:00:00'),
(4,2,2,'Ciclo de vida del software.','Comparación de modelos: cascada, espiral e incremental.',0,'2026-06-30 08:00:00','2026-06-30 08:00:00'),
(5,3,3,'Scrum y sus ceremonias.','Simulación de un sprint con tablero Kanban.',0,'2026-06-30 08:00:00','2026-06-30 08:00:00'),
(6,4,1,'Modelo Entidad-Relación.','Diseño de diagramas ER para casos reales.',0,'2026-06-30 08:05:00','2026-06-30 08:05:00'),
(7,4,2,'Lenguaje SQL: DDL y DML.','Creación de tablas y consultas SELECT básicas.',0,'2026-06-30 08:05:00','2026-06-30 08:05:00'),
(8,5,3,'Joins y subconsultas.','Práctica con bases de datos de muestra.',0,'2026-06-30 08:05:00','2026-06-30 08:05:00'),
(9,6,1,'Técnicas de elicitación de requerimientos.','Entrevistas y talleres con usuarios.',0,'2026-06-30 08:10:00','2026-06-30 08:10:00'),
(10,6,2,'Especificación de requerimientos (SRS).','Redacción de casos de uso.',0,'2026-06-30 08:10:00','2026-06-30 08:10:00'),
(11,7,3,'Diagramas de clase y secuencia.','Modelado de un sistema de biblioteca.',0,'2026-06-30 08:10:00','2026-06-30 08:10:00'),
(12,8,1,'Estructuras lineales: listas enlazadas.','Implementación en Java y Python.',0,'2026-06-30 08:15:00','2026-06-30 08:15:00'),
(13,8,2,'Pilas y colas: aplicaciones.','Simulación de navegación web con pila.',0,'2026-06-30 08:15:00','2026-06-30 08:15:00'),
(14,9,3,'Árboles binarios de búsqueda.','Inserción, eliminación y recorridos.',0,'2026-06-30 08:15:00','2026-06-30 08:15:00'),
(15,10,1,'Complejidad algorítmica Big-O.','Análisis de algoritmos de búsqueda.',0,'2026-06-30 08:20:00','2026-06-30 08:20:00'),
(16,10,2,'Búsqueda lineal y binaria.','Implementación y pruebas de rendimiento.',0,'2026-06-30 08:20:00','2026-06-30 08:20:00'),
(17,11,3,'Algoritmos de ordenamiento clásicos.','QuickSort, MergeSort y HeapSort comparados.',0,'2026-06-30 08:20:00','2026-06-30 08:20:00'),
(18,12,1,'Introducción a la IA y sus aplicaciones.','Recorrido histórico y estado del arte.',0,'2026-06-30 08:25:00','2026-06-30 08:25:00'),
(19,12,2,'Búsqueda heurística: A*.','Resolución del laberinto con A*.',0,'2026-06-30 08:25:00','2026-06-30 08:25:00'),
(20,13,3,'Regresión lineal y logística.','Entrenamiento con scikit-learn.',0,'2026-06-30 08:25:00','2026-06-30 08:25:00'),
(21,14,1,'Procesos del sistema operativo.','Estados y transiciones de procesos.',0,'2026-06-30 08:30:00','2026-06-30 08:30:00'),
(22,14,2,'Hilos y concurrencia.','Problemas del productor-consumidor.',0,'2026-06-30 08:30:00','2026-06-30 08:30:00'),
(23,15,3,'Paginación y segmentación.','Simulación de gestión de memoria.',0,'2026-06-30 08:30:00','2026-06-30 08:30:00'),
(24,16,1,'Capas del modelo OSI.','Análisis de tráfico con Wireshark.',0,'2026-06-30 08:35:00','2026-06-30 08:35:00'),
(25,16,2,'Protocolo IP, TCP y UDP.','Diferencias y casos de uso.',0,'2026-06-30 08:35:00','2026-06-30 08:35:00'),
(26,17,3,'Criptografía simétrica y asimétrica.','Implementación básica de RSA.',0,'2026-06-30 08:35:00','2026-06-30 08:35:00'),
(27,18,1,'Estructura de una página HTML5.','Maquetado de una landing page.',0,'2026-06-30 08:40:00','2026-06-30 08:40:00'),
(28,18,2,'Flexbox y Grid CSS.','Diseño de layouts responsivos.',0,'2026-06-30 08:40:00','2026-06-30 08:40:00'),
(29,19,3,'Introducción a React.','Componentes, props y estado.',0,'2026-06-30 08:40:00','2026-06-30 08:40:00'),
(30,20,1,'Funciones de agregación avanzadas.','GROUP BY, HAVING y funciones de ventana.',0,'2026-06-30 08:45:00','2026-06-30 08:45:00'),
(31,20,2,'Índices y optimización de consultas.','Uso del EXPLAIN en MySQL.',0,'2026-06-30 08:45:00','2026-06-30 08:45:00'),
(32,21,3,'Procedimientos almacenados.','Creación de stored procedures en MySQL.',0,'2026-06-30 08:45:00','2026-06-30 08:45:00'),
(33,22,1,'Clasificación de materiales de construcción.','Ensayos de tracción y compresión.',0,'2026-06-30 08:50:00','2026-06-30 08:50:00'),
(34,22,2,'Diagrama esfuerzo-deformación.','Análisis del punto de fluencia.',0,'2026-06-30 08:50:00','2026-06-30 08:50:00'),
(35,23,3,'Factor de seguridad en diseño.','Aplicación en estructuras reales.',0,'2026-06-30 08:50:00','2026-06-30 08:50:00'),
(36,24,1,'Flexión pura en vigas.','Cálculo de momentos flectores.',0,'2026-06-30 08:55:00','2026-06-30 08:55:00'),
(37,24,2,'Esfuerzo cortante en secciones.','Diagramas de corte y momento.',0,'2026-06-30 08:55:00','2026-06-30 08:55:00'),
(38,25,3,'Pandeo de columnas esbeltas.','Columnas de Euler.',0,'2026-06-30 08:55:00','2026-06-30 08:55:00'),
(39,26,1,'Diseño de vigas de concreto armado.','Cálculo a flexión y corte según ACI.',0,'2026-06-30 09:00:00','2026-06-30 09:00:00'),
(40,26,2,'Diseño de columnas con carga axial.','Diagramas de interacción.',0,'2026-06-30 09:00:00','2026-06-30 09:00:00'),
(41,27,3,'Diseño de losas en dos direcciones.','Método de los coeficientes.',0,'2026-06-30 09:00:00','2026-06-30 09:00:00'),
(42,28,1,'Presión hidrostática y empuje.','Cálculo de fuerzas sobre compuertas.',0,'2026-06-30 09:05:00','2026-06-30 09:05:00'),
(43,28,2,'Ecuación de Bernoulli y aplicaciones.','Medición de caudal con venturímetro.',0,'2026-06-30 09:05:00','2026-06-30 09:05:00'),
(44,29,3,'Pérdidas de carga en tuberías.','Fórmula de Hazen-Williams.',0,'2026-06-30 09:05:00','2026-06-30 09:05:00');
/*!40000 ALTER TABLE `silabo_tema` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- CANALES (8 canales, con temas/chats y mensajes)
-- ============================================================
LOCK TABLES `canal` WRITE;
/*!40000 ALTER TABLE `canal` DISABLE KEYS */;
INSERT INTO `canal` VALUES
(1,'FAING General','Canal oficial de la Facultad de Ingeniería',1,'administrativo','activo','#1d4ed8',NULL,'2026-06-20 09:00:00'),
(2,'Sistemas e Informática','Noticias y anuncios de Ing. de Sistemas',7,'docente','activo','#7c3aed',NULL,'2026-06-20 09:05:00'),
(3,'Proyectos de Tesis','Coordinación de proyectos de investigación',9,'docente','activo','#059669',NULL,'2026-06-20 09:10:00'),
(4,'Olimpiadas UPT 2026','Canal de comunicación oficial de las olimpiadas',1,'administrativo','activo','#dc2626',NULL,'2026-06-21 08:00:00'),
(5,'Bienestar Estudiantil','Psicología, policlínico y apoyo al estudiante',1,'administrativo','activo','#d97706',NULL,'2026-06-21 08:10:00'),
(6,'Biblioteca Digital','Recursos académicos y bases de datos',1,'administrativo','activo','#0891b2',NULL,'2026-06-22 08:00:00'),
(7,'Cafeterías UPT','Menús, ofertas y pedidos especiales',1,'administrativo','activo','#65a30d',NULL,'2026-06-22 08:30:00'),
(8,'Egresados FAING','Red de contactos de egresados de ingeniería',1,'administrativo','activo','#9333ea',NULL,'2026-06-23 09:00:00');
/*!40000 ALTER TABLE `canal` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `canal_miembro` WRITE;
/*!40000 ALTER TABLE `canal_miembro` DISABLE KEYS */;
INSERT INTO `canal_miembro` VALUES
(1,1,1,'admin','2026-06-20 09:00:00'),
(2,1,7,'miembro','2026-06-20 09:05:00'),
(3,1,8,'miembro','2026-06-20 09:06:00'),
(4,1,9,'miembro','2026-06-20 09:07:00'),
(5,1,10,'miembro','2026-06-20 09:08:00'),
(6,1,2,'miembro','2026-06-20 09:10:00'),
(7,2,7,'admin','2026-06-20 09:05:00'),
(8,2,8,'miembro','2026-06-20 09:10:00'),
(9,2,9,'miembro','2026-06-20 09:11:00'),
(10,2,10,'miembro','2026-06-20 09:12:00'),
(11,2,2,'miembro','2026-06-20 09:15:00'),
(12,3,9,'admin','2026-06-20 09:10:00'),
(13,3,7,'miembro','2026-06-20 09:15:00'),
(14,3,8,'miembro','2026-06-20 09:16:00'),
(15,3,2,'miembro','2026-06-20 09:17:00'),
(16,4,1,'admin','2026-06-21 08:00:00'),
(17,4,7,'miembro','2026-06-21 08:01:00'),
(18,4,2,'miembro','2026-06-21 08:02:00'),
(19,5,1,'admin','2026-06-21 08:10:00'),
(20,5,2,'miembro','2026-06-21 08:11:00'),
(21,5,7,'miembro','2026-06-21 08:12:00'),
(22,6,1,'admin','2026-06-22 08:00:00'),
(23,6,2,'miembro','2026-06-22 08:01:00'),
(24,7,1,'admin','2026-06-22 08:30:00'),
(25,7,2,'miembro','2026-06-22 08:31:00'),
(26,8,1,'admin','2026-06-23 09:00:00'),
(27,8,7,'miembro','2026-06-23 09:01:00');
/*!40000 ALTER TABLE `canal_miembro` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `canal_tema` WRITE;
/*!40000 ALTER TABLE `canal_tema` DISABLE KEYS */;
INSERT INTO `canal_tema` VALUES
-- Canal 1: FAING General (4 temas)
(1,1,'Anuncios','Comunicados oficiales de la facultad',0,'2026-06-20 09:00:00'),
(2,1,'Eventos','Actividades académicas y extracurriculares',1,'2026-06-20 09:00:00'),
(3,1,'Consultas Generales','Preguntas frecuentes y respuestas',2,'2026-06-20 09:00:00'),
(4,1,'Recursos','Documentos y materiales de apoyo',3,'2026-06-20 09:00:00'),
-- Canal 2: Sistemas e Informática (4 temas)
(5,2,'Novedades TI','Noticias del mundo tecnológico',0,'2026-06-20 09:05:00'),
(6,2,'Proyectos','Seguimiento de proyectos del semestre',1,'2026-06-20 09:05:00'),
(7,2,'Soporte','Ayuda técnica entre compañeros',2,'2026-06-20 09:05:00'),
(8,2,'Herramientas','IDEs, frameworks y librerías recomendadas',3,'2026-06-20 09:05:00'),
-- Canal 3: Proyectos de Tesis (3 temas)
(9,3,'Asesorías','Coordinación con asesores',0,'2026-06-20 09:10:00'),
(10,3,'Avances','Compartir avances semanales',1,'2026-06-20 09:10:00'),
(11,3,'Sustentaciones','Fechas y preparación de defensa',2,'2026-06-20 09:10:00'),
-- Canal 4: Olimpiadas UPT 2026 (5 temas)
(12,4,'Resultados','Marcadores y resultados en tiempo real',0,'2026-06-21 08:00:00'),
(13,4,'Inscripciones','Proceso y estado de inscripciones',1,'2026-06-21 08:00:00'),
(14,4,'Disciplinas','Info por deporte o actividad',2,'2026-06-21 08:00:00'),
(15,4,'Fotografías','Fotos de los eventos',3,'2026-06-21 08:00:00'),
(16,4,'Comentarios','Apoyo y aliento entre facultades',4,'2026-06-21 08:00:00'),
-- Canal 5: Bienestar Estudiantil (3 temas)
(17,5,'Citas Disponibles','Horarios disponibles de psicología y policlínico',0,'2026-06-21 08:10:00'),
(18,5,'Charlas y Talleres','Actividades de bienestar programadas',1,'2026-06-21 08:10:00'),
(19,5,'Apoyo Emocional','Espacio seguro de escucha',2,'2026-06-21 08:10:00'),
-- Canal 6: Biblioteca Digital (3 temas)
(20,6,'Novedades','Nuevos libros y recursos incorporados',0,'2026-06-22 08:00:00'),
(21,6,'Bases de Datos','IEEE, Scopus, ScienceDirect y más',1,'2026-06-22 08:00:00'),
(22,6,'Solicitudes','Adquisición de nuevos títulos',2,'2026-06-22 08:00:00'),
-- Canal 7: Cafeterías UPT (3 temas)
(23,7,'Menú del Día','Platos disponibles hoy',0,'2026-06-22 08:30:00'),
(24,7,'Promociones','Descuentos y combos especiales',1,'2026-06-22 08:30:00'),
(25,7,'Sugerencias','Mejoras y nuevos productos',2,'2026-06-22 08:30:00'),
-- Canal 8: Egresados FAING (3 temas)
(26,8,'Oportunidades Laborales','Ofertas de trabajo para egresados',0,'2026-06-23 09:00:00'),
(27,8,'Reuniones','Encuentros presenciales y virtuales',1,'2026-06-23 09:00:00'),
(28,8,'Noticias','Logros de exalumnos',2,'2026-06-23 09:00:00');
/*!40000 ALTER TABLE `canal_tema` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `canal_mensaje` WRITE;
/*!40000 ALTER TABLE `canal_mensaje` DISABLE KEYS */;
INSERT INTO `canal_mensaje` (`IdMensaje`,`IdCanal`,`IdTema`,`IdMensajeRespuesta`,`IdUsuario`,`Contenido`,`ImagenUrl`,`FechaEnvio`) VALUES
-- Canal 1 - Tema Anuncios
(1,1,1,NULL,1,'Bienvenidos al canal oficial de la Facultad de Ingeniería. Aquí encontrarán los comunicados más importantes del semestre 2026-I.',NULL,'2026-06-20 09:30:00'),
(2,1,1,NULL,7,'Estimados estudiantes, recuerden que el plazo para la entrega del sílabo vence el 5 de julio.',NULL,'2026-06-21 10:00:00'),
(3,1,1,NULL,1,'Se comunica que el 10 de julio se realizará el examen parcial de todas las asignaturas. Revisen los horarios en el portal.',NULL,'2026-06-22 08:00:00'),
-- Canal 1 - Tema Eventos
(4,1,2,NULL,1,'¡Las Olimpiadas Interfacultades 2026 arrancan este lunes! FAING los espera con todo.',NULL,'2026-06-21 09:00:00'),
(5,1,2,NULL,2,'¿Habrá transmisión en vivo de los partidos de fútbol?',NULL,'2026-06-21 09:15:00'),
(6,1,2,5,1,'Sí, se transmitirán por el canal de YouTube de la universidad. Estaremos compartiendo el enlace.',NULL,'2026-06-21 09:20:00'),
-- Canal 2 - Tema Novedades TI
(7,2,5,NULL,7,'Actualización importante: Python 3.13 ya está disponible con mejoras significativas en el JIT compiler.',NULL,'2026-06-22 10:00:00'),
(8,2,5,NULL,8,'También salió Laravel 11.x con soporte para PHP 8.3. Recomiendo actualizar los proyectos.',NULL,'2026-06-22 10:30:00'),
(9,2,5,NULL,2,'¿Alguien ya probó los nuevos modelos de IA de Anthropic? Increíbles para asistir en el código.',NULL,'2026-06-22 11:00:00'),
-- Canal 2 - Tema Proyectos
(10,2,6,NULL,7,'Por favor compartan el avance de sus proyectos del curso de Ingeniería de Software antes del viernes.',NULL,'2026-06-23 09:00:00'),
(11,2,6,NULL,2,'Aquí el link de nuestro repositorio: github.com/grupo5/proyecto-is. Cualquier feedback es bienvenido.',NULL,'2026-06-23 09:30:00'),
(12,2,6,11,9,'Revisé el repositorio, muy buen trabajo chicos. Sugiero agregar pruebas unitarias al módulo de autenticación.',NULL,'2026-06-23 10:00:00'),
-- Canal 3 - Tema Asesorías
(13,3,9,NULL,9,'Estimados tesistas, los espacios de asesoría de julio están disponibles. Escriban aquí para reservar su slot.',NULL,'2026-06-20 14:00:00'),
(14,3,9,NULL,2,'Dr. Martínez, ¿podría asesoría el martes 8 de julio a las 10am?',NULL,'2026-06-20 14:30:00'),
(15,3,9,14,9,'Confirmado. Los espero en mi oficina, módulo F piso 2.',NULL,'2026-06-20 15:00:00'),
-- Canal 4 - Tema Resultados
(16,4,12,NULL,1,'RESULTADOS - Fútbol Varones Grupo A: FAING 3 - FADE 1. ¡Gran partido!',NULL,'2026-06-24 18:00:00'),
(17,4,12,NULL,7,'Vóleibol Femenino: FCJP 2 - FAING 1. Nos recuperamos en la siguiente jornada.',NULL,'2026-06-24 19:00:00'),
(18,4,12,NULL,2,'Atletismo 100m Varones: 1° FAING - 2° FCSB - 3° FACS. ¡Orgullo de la facultad!',NULL,'2026-06-24 20:00:00'),
-- Canal 5 - Tema Apoyo Emocional
(19,5,19,NULL,1,'Recuerda que el equipo de psicología está disponible de lunes a viernes de 8am a 5pm. No estás solo/a.',NULL,'2026-06-21 09:00:00'),
(20,5,19,NULL,2,'Gracias por el espacio. A veces la presión del semestre es agotadora. ¿Cómo agendo una cita?',NULL,'2026-06-22 11:00:00'),
(21,5,19,20,1,'Puedes agendar directamente desde el módulo de Psicología en la plataforma. ¡Cuídate mucho!',NULL,'2026-06-22 11:15:00'),
-- Canal 7 - Menú del Día
(22,7,23,NULL,1,'MENU DE HOY - Cafeteria FAING: Cafe Americano S/3.50 | Sandwich Mixto S/5.00 | Combo desayuno S/7.00',NULL,'2026-06-30 07:30:00'),
(23,7,23,NULL,2,'¿Tienen opción vegetariana hoy?',NULL,'2026-06-30 07:45:00'),
(24,7,23,23,1,'Sí, tenemos ensalada de quinua y fruta. S/4.50.',NULL,'2026-06-30 07:50:00'),
-- Canal 8 - Oportunidades Laborales
(25,8,26,NULL,1,'Empresa Tec-Perú busca practicantes de Ing. de Sistemas. Enviar CV a rrhh@tecperu.pe hasta el 15 de julio.',NULL,'2026-06-25 09:00:00'),
(26,8,26,NULL,7,'También hay convocatoria en el BCP para analistas de datos junior. Requisito: Python y SQL.',NULL,'2026-06-25 09:30:00'),
(27,8,26,NULL,2,'¡Muchas gracias! Ya envié mi CV a ambas.',NULL,'2026-06-25 10:00:00');
/*!40000 ALTER TABLE `canal_mensaje` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- CITAS PSICOLOGÍA (5 registros)
-- ============================================================
LOCK TABLES `cita_psicologia` WRITE;
/*!40000 ALTER TABLE `cita_psicologia` DISABLE KEYS */;
INSERT INTO `cita_psicologia` VALUES
(1,2,1,1,'2026-07-07','Estrés académico y manejo de la ansiedad ante los exámenes','Pendiente','2026-06-28 10:30:00'),
(2,12,1,3,'2026-07-07','Dificultades de adaptación al entorno universitario','Confirmada','2026-06-27 09:15:00'),
(3,13,2,2,'2026-07-08','Orientación vocacional y toma de decisiones','Pendiente','2026-06-28 14:00:00'),
(4,19,2,4,'2026-07-08','Problemas de concentración y hábitos de estudio','Confirmada','2026-06-26 11:45:00'),
(5,24,1,5,'2026-07-09','Gestión emocional y relaciones interpersonales','Pendiente','2026-06-29 08:20:00');
/*!40000 ALTER TABLE `cita_psicologia` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- CITAS POLICLÍNICO (5 registros)
-- ============================================================
LOCK TABLES `cita_policlinico` WRITE;
/*!40000 ALTER TABLE `cita_policlinico` DISABLE KEYS */;
INSERT INTO `cita_policlinico` VALUES
(1,2,1,1,1,'2026-07-07','Control de presión arterial y chequeo general','Pendiente','2026-06-28 09:00:00'),
(2,12,3,1,2,'2026-07-07','Dolor de cabeza recurrente y fatiga','Confirmada','2026-06-27 08:30:00'),
(3,13,5,1,3,'2026-07-08','Consulta por gastritis y dolor abdominal','Pendiente','2026-06-28 13:00:00'),
(4,17,2,1,4,'2026-07-08','Revisión de exámenes de laboratorio','Confirmada','2026-06-26 10:00:00'),
(5,19,9,1,1,'2026-07-09','Control de vista y evaluación optométrica','Pendiente','2026-06-29 07:45:00');
/*!40000 ALTER TABLE `cita_policlinico` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- CAFETERÍA - PEDIDOS Y ARTÍCULOS (5 pedidos)
-- ============================================================
LOCK TABLES `cafeteria_pedido` WRITE;
/*!40000 ALTER TABLE `cafeteria_pedido` DISABLE KEYS */;
INSERT INTO `cafeteria_pedido` VALUES
(1,1,2,8.50,'confirmado','QR-2026-001',NULL,'OP-102341',NULL,'2026-06-28 07:45:00','2026-06-28 07:50:00',NULL),
(2,1,12,5.00,'entregado','QR-2026-002',NULL,'OP-102342',NULL,'2026-06-28 08:10:00','2026-06-28 08:12:00','2026-06-28 08:30:00'),
(3,2,13,4.00,'pendiente_revision','QR-2026-003',NULL,NULL,NULL,'2026-06-28 09:00:00',NULL,NULL),
(4,1,19,10.00,'confirmado','QR-2026-004',NULL,'OP-102344',NULL,'2026-06-29 07:30:00','2026-06-29 07:35:00',NULL),
(5,2,24,9.00,'entregado','QR-2026-005',NULL,'OP-102345',NULL,'2026-06-29 08:00:00','2026-06-29 08:03:00','2026-06-29 08:25:00');
/*!40000 ALTER TABLE `cafeteria_pedido` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `cafeteria_pedido_item` WRITE;
/*!40000 ALTER TABLE `cafeteria_pedido_item` DISABLE KEYS */;
INSERT INTO `cafeteria_pedido_item` VALUES
(1,1,1,1,3.50),
(2,1,2,1,5.00),
(3,2,2,1,5.00),
(4,3,3,1,4.00),
(5,4,1,2,3.50),
(6,4,2,1,5.00),
(7,5,3,1,4.00),
(8,5,4,1,4.50);
/*!40000 ALTER TABLE `cafeteria_pedido_item` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- OLIMPIADAS - EDICIONES ANTERIORES (2 ediciones extra)
-- ============================================================
LOCK TABLES `olimpiada_edicion` WRITE;
/*!40000 ALTER TABLE `olimpiada_edicion` DISABLE KEYS */;
INSERT INTO `olimpiada_edicion` VALUES
(2,'Olimpiadas Interfacultades 2025',2025,1,2025,2,'finalizada','2025-03-01 00:00:00','2025-04-01 00:00:00','2025-04-14','2025-04-18','Edición 2025 finalizada exitosamente. FAING se coronó campeón general.','2025-03-01 07:00:00'),
(3,'Olimpiadas Interfacultades 2024',2024,1,2024,2,'finalizada','2024-03-01 00:00:00','2024-04-01 00:00:00','2024-04-15','2024-04-19','Edición 2024. FACS ganó el torneo de vóleibol por primera vez.','2024-03-01 07:00:00');
/*!40000 ALTER TABLE `olimpiada_edicion` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- OLIMPIADAS - POSTS ADICIONALES (3 posts más)
-- ============================================================
LOCK TABLES `olimpiada_post` WRITE;
/*!40000 ALTER TABLE `olimpiada_post` DISABLE KEYS */;
INSERT INTO `olimpiada_post` VALUES
(7,1,'Vóleibol: FCJP sorprende en semifinales','La Facultad de Ciencias Jurídicas y Políticas dio la sorpresa del día al eliminar a FCSB en tres sets (25-22, 22-25, 15-10) y avanzar a las semifinales de vóleibol femenino. La final se disputará el próximo sábado.','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRstJR39GC8Xdncgm48MUf8DmDcJ8PTYT6g4Q5oX6QH_HhWiFQ05NnQ6dBc&s=10','Prensa Deportiva UPT','2026-06-25 10:00:00'),
(8,1,'Atletismo: nuevas marcas en los 400m','El estudiante Miguel Vargas de FAING estableció una nueva marca del torneo en los 400m planos con un tiempo de 52.4 segundos, superando el récord de la edición anterior. La competencia de pistas continúa mañana en el Coliseo UPT.','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQEhkEc4dRKS2VFnMwNMd6wZCzV6Ub9rMlTH-6pOxNDZPys0OETJSqYGgVL&s=10','Comité Olímpico UPT','2026-06-25 16:00:00'),
(9,1,'Resumen del día 3: Ajedrez y Tenis de Mesa','Intensas jornadas de ajedrez y tenis de mesa marcaron el tercer día de competencia. FADE domina el tablero con 3 de 4 puntos en la primera ronda. En tenis de mesa, la FACS lidera la categoría damas individual con dos victorias consecutivas.','https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSv3xLVtRpASQ1ypY1iHSoIOyhhT3T6BzQBXzKzSpb8MCssgG8NcxKd_YRu&s=10','Comité Olímpico UPT','2026-06-26 20:00:00');
/*!40000 ALTER TABLE `olimpiada_post` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- OLIMPIADAS - COMENTARIOS EN POSTS (más comentarios)
-- ============================================================
LOCK TABLES `olimpiada_comentario` WRITE;
/*!40000 ALTER TABLE `olimpiada_comentario` DISABLE KEYS */;
INSERT INTO `olimpiada_comentario` VALUES
-- Posts existentes (4, 5, 6)
(9,4,2,'¡Qué emoción! Esta edición promete ser la mejor de todas. ¡Vamos todos!','2026-06-25 08:00:00'),
(10,4,7,'FAING siempre presente. Este año repetimos el título, no hay duda.','2026-06-25 08:30:00'),
(11,4,8,'La organización del comité olímpico este año ha sido excelente. Felicitaciones.','2026-06-25 09:00:00'),
(12,5,9,'Carlos Mamani es el mejor jugador del torneo, ese doblete fue increíble.','2026-06-25 09:30:00'),
(13,5,10,'Buen partido, pero FADE va a contraatacar en la próxima jornada. ¡Atentos!','2026-06-25 10:00:00'),
(14,6,11,'El atletismo siempre ha sido el punto fuerte de FAING. ¡100m seguros!','2026-06-25 10:30:00'),
(15,6,2,'El coliseo estaba lleno, fue una jornada espectacular. Gracias al comité.','2026-06-25 11:00:00'),
-- Nuevos posts (7, 8, 9)
(16,7,12,'¡Impresionante la actuación de FCJP! Nadie lo esperaba. El vóleibol está muy parejo.','2026-06-25 11:30:00'),
(17,7,13,'El nivel este año es mucho más alto que en 2025. Se nota el entrenamiento.','2026-06-25 12:00:00'),
(18,7,2,'¡Vamos FCJP! Tienen todo para ganar la final del sábado.','2026-06-25 12:30:00'),
(19,8,14,'Nueva marca del torneo, eso es histórico. Miguel Vargas es un crack.','2026-06-25 17:00:00'),
(20,8,8,'52.4 segundos a nivel universitario es muy buena marca. ¡Orgullo!','2026-06-25 17:30:00'),
(21,9,9,'El ajedrez siempre tan emocionante pero tan poco reconocido. ¡Apoyo total a los jugadores!','2026-06-26 21:00:00'),
(22,9,2,'El tenis de mesa damas va a ser muy reñido. La FACS está jugando muy bien.','2026-06-26 21:30:00');
/*!40000 ALTER TABLE `olimpiada_comentario` ENABLE KEYS */;
UNLOCK TABLES;

-- ============================================================
-- RESERVAS DE ESPACIOS (5 solicitudes)
-- ============================================================
LOCK TABLES `reserva` WRITE;
/*!40000 ALTER TABLE `reserva` DISABLE KEYS */;
INSERT INTO `reserva` VALUES
(2,7,9,3,3,'2026-07-07','2026-06-30 09:00:00','Clase práctica de Ingeniería de Software I - Laboratorio de Sistemas',26,'Pendiente'),
(3,8,10,5,5,'2026-07-08','2026-06-30 09:10:00','Laboratorio de Estructuras de Datos - Práctica con listas y árboles',28,'Aprobada'),
(4,9,14,7,7,'2026-07-09','2026-06-30 09:20:00','Clase teórica de Redes de Computadoras',30,'Pendiente'),
(5,10,11,2,11,'2026-07-10','2026-06-30 09:30:00','Laboratorio de Mecánica de Materiales - Ensayos de tracción',25,'Aprobada'),
(6,7,15,4,3,'2026-07-11','2026-06-30 09:40:00','Sesión de asesoría de proyecto final de Ingeniería de Software',20,'Rechazada');
/*!40000 ALTER TABLE `reserva` ENABLE KEYS */;
UNLOCK TABLES;
