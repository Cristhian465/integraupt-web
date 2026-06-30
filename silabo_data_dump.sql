-- ============================================================
-- IntegraUPT - Datos reales de sílabos (dump 2026-06-29)
-- ============================================================
-- Ejecutar DESPUÉS de correr todas las migraciones del
-- servicio-silabo (incluida la nueva: 2026_06_29_000001)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';

-- -----------------------------------------------------------
-- silabo
-- -----------------------------------------------------------
INSERT INTO `silabo`
  (`IdSilabo`,`CodigoCurso`,`NombreCurso`,`CicloNumero`,`Semestre`,
   `Horas`,`Creditos`,`Docente`,`CorreoDocente`,`HorarioCursoId`,
   `DiasXSemana`,`ArchivoPdf`,`FechaCarga`,`Estado`)
VALUES
  (2,'SI-786','Programación Web I',7,'2026-I',6,4,'Ing. Tito Fernando Ale Nieto',NULL,NULL,1,
   'storage/silabos/silabo_SI-786_2026-I_1782688861.pdf','2026-06-28',1),
  (4,'EG-121','Comunicación I',1,'2026-I',5,4,'s : Mg. Américo Alca Gómez',NULL,NULL,1,
   'storage/silabos/silabo_EG-121_2026-I_1782690271.pdf','2026-06-28',1),
  (5,'INE-166','Matemática I',1,'2023---I',6,4,'s : Dr. Henry Edgardo Nina Mendoza',NULL,NULL,3,
   'storage/silabos/silabo_INE-166_2023---I_1782694924.pdf','2026-06-28',1);

-- -----------------------------------------------------------
-- silabo_unidad
-- -----------------------------------------------------------
INSERT INTO `silabo_unidad` (`IdUnidad`,`SilaboId`,`Numero`,`Nombre`,`HorasTotal`) VALUES
  (3,2,1,'FUNDAMENTOS DEL',36),
  (4,2,2,'DESARROLLO WEB CON BASE',36),
  (5,2,3,'EXTRACCIÓN DE INFORMACIÓN',30),
  (9,4,1,'TEORÍA DE LA COMUNICACIÓN',30),
  (10,4,2,'COMUNICACIÓN ORAL',30),
  (11,4,3,'COMUNICACIÓN ESCRITA',25),
  (12,5,1,'FUNCIONES REALES DE VARIABLE REAL (',30),
  (13,5,2,'LÍMITES DE FUNCIONES DE UNA VARIABLE (',36),
  (14,5,3,'DERIVADAS Y DIFERENCIALES (',36);

-- -----------------------------------------------------------
-- silabo_tema
-- -----------------------------------------------------------
INSERT INTO `silabo_tema` (`IdTema`,`UnidadId`,`Semana`,`ContenidoConceptual`,`ContenidoProcedimental`,`Orden`) VALUES
(2,3,1,'Introducción al curso Internet y la Web, historia Fundamento del desarrollo Web Desarrollo FrontEnd/BackEnd Arquitectura Web Repaso HTML5 Repaso CSS3 Editor Visual Studio Code Instalación del entorno de trabajo: Servidores Web. Instalación Configuración de un','Diferencia los',1),
(3,4,1,'- Base de Datos: MySQL, MariaBD - Funcionamiento de una aplicación Web - PhpMyadmin, HeidiSQL y otros - Uso de PHP y MySQL? - El lenguaje SQL - Qué es DDL, DML? - Creación, modificar, eliminar Base de Datos, Tablas - SELECT, UPDATE, DELETE - Consultas con SQL','Maneja los conceptos impartidos sobre las aplicaciones web con base de datos. Experimenta el uso del lenguaje SQL en la creación de una BD Crea y diseña una base de datos usando herramientas del entorno de desarrollo Web. 2',1),
(4,4,2,'PHP con Bases de Datos Procedimental, PDO y MySQLi Funciones conexión con MariaDB Modificaciones de Datos CRUD Listar registros Insertar un Registro','Aplica los conceptos impartidos sobre las formas de conectar una base de datos usando PHP en un caso práctico Realiza y presenta la solución de lo propuesto en el laboratorio. 3',2),
(5,4,3,'Modificaciones de Datos CRUD Modificar un Registro Borrar un Registro Búsqueda y consulta Uso de filtros','Aplica los conceptos impartidos sobre CRUD y base de datos. Realiza y presenta la solución de lo propuesto en el laboratorio. 4',3),
(6,4,4,'Uso Sesiones, Funciones PHP para Sesiones Variables de sesión Autenticación de Usuarios','Aplica los conceptos impartidos sobre el uso de sesiones y autenticación de usuarios. Realiza y presenta la solución de lo propuesto en el laboratorio. 5',4),
(7,4,5,'Trabajar con varias tablas: relaciones Mantenimiento de varias tablas Uso de Procedimientos almacenados','Maneja los conceptos SQL para trabajar con varias tablas. Realiza y presenta la solución de lo propuesto en el laboratorio. 6',5),
(8,4,6,'Revisión de caso practico Revisión del Proyecto del Curso Evaluación Teórica - Practica','Demuestra el avance de su proyecto, según lo planificado previamente. Presenta la evaluación teórica - practica',6),
(9,5,1,'Seguridad Web Carrito de Compras Envió de correo desde PHP','Aplica los conceptos sobre la seguridad Web en la solución de lo propuesto en el laboratorio. 2',1),
(10,5,2,'Creación de reportes PDF Generación de gráficos','Maneja la creación de reportes PDF y generación de gráficos en PHP. Resuelve y presenta la solución de lo propuesto en el laboratorio. 3',2),
(11,5,3,'Modelo MVC Como Implementar MVC en PHP','Prueba el uso de Modelo Vista Controlador en caso práctico. Resuelve y presenta la solución de lo propuesto en el laboratorio 4',3),
(12,5,4,'XML y RSS Servicios Web','Observa los conceptos sobre uso de XML y Servicios Web 5',4),
(13,5,5,'Presentación y exposición del Proyecto Evaluación Final','Realiza la exposición final de su proyecto del curso Resuelve y presenta la evaluación final.',5),
(29,9,1,'− La comunicación humana: concepto, elementos  que intervienen en el proceso de comunicación.  − Clases de comunicación. Relación entre  Comunicación y la Ingeniería.  − Esquematiza la diferencia entre comunicación lingüística  y no lingüística.  − Registra las diferencias y características de la  comunicación humana y no humana.  2',NULL,1),
(30,9,2,'− Formas de comunicación. Estrategias  comunicativas.  − Cualidades básicas de una comunicación  adecuada.  − Establece las principales cualidades de una buena  comunicación.  − Expresa su opinión respecto a la relación entre la  Comunicación y la Ingeniería.    3',NULL,2),
(31,9,3,'− Naturaleza del lenguaje. Concepto. Propiedades  universales del lenguaje. Funciones del  lenguaje.  − Relación entre lengua, habla y norma.  − El dialecto.    − Participa en Sociodramas para ilustrar las funciones del  lenguaje; así como también las diferentes formas de  comunicación.    4',NULL,3),
(32,9,4,'− Niveles de la lengua.  − Diversificación del español en el Perú, lenguas  peruanas y el bilingüismo. La realidad lingüística  del Perú.    −','Elabora un mapa conceptual y formula ejemplos referidos  a la Ingeniería.  − Elabora diapositivas acerca de la Realidad Lingüística del  Perú.        5',4),
(33,9,5,'− Niveles, técnicas y estrategias de lectura.  − Lecturas sugeridas. Análisis y crítica en la  lectura.      − Lee y comprende textos, desarrollando un cuestionario    6',NULL,5),
(34,9,6,'− Control de lectura  − Examen de primera unidad  −','Desarrolla pruebas a respuesta abierta y objetiva',6),
(35,10,7,'− Oratoria: Base teórica.  − Técnicas de Oratoria: Finalidad e importancia    −','Elabora y hace público un discurso respecto a la  relevancia de la carrera profesional.    8',7),
(36,10,8,'− Oratoria y liderazgo  − Lectura: Noam Chomsky solo para principiantes.  − Las dos caras de la comunicación de Ismael  Cala y Camilo Cruz  − Participa de un taller que diferencia los tipos de liderazgo  y su relación con el asertividad.  −','Explica el contenido de la lectura sugerida de Noam  Chomsky solo para principiantes y Las dos caras de la  comunicación.  9',8),
(37,10,9,'− Corporalidad y comunicación:  Cualidades del orador, estilo y posturas  corporales.  − El discurso: estructura y contenido.  − Dirige una reflexión a sus compañeros considerando lo  expresado en clase.  − Redacta y expone un discurso con toda la formalidad ante  sus compañeros.  10',NULL,9),
(38,10,10,'− La Exposición: concepto, tipos, características.  − Disertaciones.    − Planifica y dirige una exposición utilizando recursos  multimedia.  − Planifica una disertación sobre un tema libre.    11',NULL,10),
(39,10,11,'− Las dinámicas grupales y el trabajo en equipo.  Concepto, tipos de dinámicas, proceso de  formación de grupos: Panel, foro, debate, mesa  redonda, simposio, seminario.  − Eficacia y rendimiento del grupo, actitudes  favorables y la persuasión.    − Planifica el desarrollo de dinámicas grupales, a través de  un tema actual que motive a sus compañeros.  −','Elabora una hoja resumen sobre la dinámica grupal de sus  compañeros participantes.  12',11),
(40,10,12,'− Exposiciones y aplicación de técnicas grupales.  − Examen de segunda unidad  − Expone utilizando alguna de las técnicas grupales.  −','Desarrolla una prueba con respuestas a desarrollo.',12),
(41,11,13,'− Criterios de corrección idiomática. Ortografía de  la sílaba y la palabra.  − Tildación y signos de puntuación: coma, punto y  coma y punto.    −','Desarrolla prácticas dirigidas y calificadas en donde  progresivamente aplique las reglas ortográficas de silabeo  y tildaciones: robúrica, tópica y diacrítica.  − Aplica las reglas ortográficas de la palabra en prácticas  dirigidas y calificadas.    14',13),
(42,11,14,'− El texto: tipos, formatos y estructuras.    − Propiedades del texto (coherencia y cohesión).  Control de lectura  − Distingue los tipos, formatos y estructuras de texto; a  través de prácticas calificadas.  − Redacta y responde textos con coherencia y cohesión.  15',NULL,14),
(43,11,15,'− Redacción: concepto, importancia. Procesos de  escritura (planificación, textualización y  revisión). Características, habilidades e  importancia de la redacción en Ingeniería  − Aplicación de normas APA. Citas y referencias  bibliográficas  − El ensayo (estructura)    −','Identifica las normativas más importantes para la  Redacción.  − Mejora su texto académico (ensayo) mediante el uso de  normas APA.  − Planifica y redacta un ensayo breve sobre un tema  relacionado a su carrera profesional, aplicando  correctamente las reglas ortográficas y gramaticales y  procesos de escritura.  16',15),
(44,11,16,'− Documentos administrativos: La solicitud, el  memorial.  − El oficio simple y circular. La carta. Tipos de  carta. El informe técnico, memorando, acta.  Documento informativo: currículo vitae.  − Documentos académicos: informes y  monografías  − Revisión y publicación de textos académicos  − Redacta documentos administrativos respetando su  estructura y aplicando criterios de corrección ortográfica.    − Corrige sus textos e identifica diversos espacios para la  publicación de textos.  17',NULL,16),
(45,11,17,'− Control de lectura  − Examen de tercera unidad    −','Desarrolla prueba de escritura.',17),
(46,12,1,'Presentación de sílabo.  Introducción al curso de Matemática I.  Evaluación de entrada.  Descripción del silabo de manera detallada y el sistema  de evaluación.','Resuelve la prueba escrita de entrada.  2',1),
(47,12,2,'Definición de relación y función.  Discusión de una gráfica de una relación  en R 2 .  Gráfica de una función.','Diferencia las relaciones de las funciones, identifica  sus clases, y determina los elementos de las  relaciones.  Representa gráficamente las relaciones y funciones.  3',2),
(48,12,3,'Funciones: criterios para el cálculo del  dominio y rango de una función.  función definida por más de una  ecuación (trozos).  Determina analíticamente el dominio y rango de una  función.  Grafica funciones restringidas para determinar su  dominio y rango.','Resuelve y calcula dominio y rango  de una función.  4',3),
(49,12,4,'Funciones especiales: constante,  identidad, lineal, valor absoluto, máximo  entero, cuadrática, polinómicas, racional,  logarítmicas, exponenciales,  trigonométricas, hiperbólicas, otras.  Grafica diversos tipos de funciones.  Forma grupos de trabajo para realizar exposiciones de  funciones especiales.  5',NULL,4),
(50,12,5,'Operaciones algebraicas con funciones.  Composición de funciones.  Funciones periódicas.  Funciones inyectivas, sobreyectivas y  biyectivas.  Función Inversa.  Evaluación I Unidad  Halla la función respuesta luego de realizar operaciones  con ellas, calculando el dominio general.','Realiza la composición de funciones, determinando el  dominio de la compuesta.  Prueba si una función dada tiene o no función inversa,  conociendo su clase.   Resuelve una prueba escrita.',5),
(51,13,6,'Definición intuitiva y formal de límite.  Teoremas sobre límites de funciones.  Opera, límites mediante procedimiento analítico y  gráfico.  7',NULL,6),
(52,13,7,'Técnicas para calcular límites. Formas  Indeterminadas.','Resuelve problemas aplicando la definición y  propiedades.  8',7),
(53,13,8,'Límites al Infinito.  Límites infinitos.  Límites Trigonométricos','Resuelve problemas aplicando propiedades, teoremas  y técnicas operativas para calcular límites infinitos y  trigonométricos.  9',8),
(54,13,9,'Límites de funciones exponencial y  logarítmica.  Asíntotas Horizontales, Verticales y  oblicuas.','Resuelve problemas aplicando propiedades y  teoremas para calcular límites exponenciales y  logarítmicos.  Determina las asíntotas de una curva dada.  10',9),
(55,13,10,'Límites Laterales.  Continuidad de una Función.  Determina la existencia de los límites considerando los  límites laterales.','Analiza la continuidad de una función.  11',10),
(56,13,11,'Evaluación II Unidad','Resuelve una prueba escrita.',11),
(57,14,12,'Definición de derivada.  Interpretación geométrica.  La derivada como razón de cambio.  La derivada como función.','Aplica propiedades de límites para calcular la derivada  de una función.  Realiza operaciones con derivadas y diferenciales.  13',12),
(58,14,13,'Reglas de derivación.  Regla de la cadena.  Reglas generales de derivación.  Determina la derivada de una función, aplicando las  reglas prácticas de derivación.  14',NULL,13),
(59,14,14,'Derivación implícita  Derivadas de orden superior.  Ecuación de la recta tangente y la normal  a una curva.','Aplica métodos para derivar expresiones implícitas.  Calcula la derivada de una función de orden superior.  Determina las ecuaciones de las rectas, tangente y  normal a una curva en un punto dado.  15',14),
(60,14,15,'Valores máximos y mínimos.  Teorema del valor medio.  La regla de L''Hospital.','Resuelve problemas de máximos y mínimos aplicando  el criterio de la primera y segunda derivada.  Aplica L''Hospital para determinar el límite de formas  indeterminadas.  16',15),
(61,14,16,'La diferencial.  La diferencial como aproximación del  incremento de una función.  Antiderivada.  Calcula diferenciales y aproxima el incremento y  decremento de una función.  Calcula antiderivadas.  17',NULL,16),
(62,14,17,'Evaluación de III Unidad.','Resuelve una prueba escrita.',17);

-- -----------------------------------------------------------
-- avance_tema
-- -----------------------------------------------------------
INSERT INTO `avance_tema`
  (`IdAvance`,`SilaboTemaId`,`DocenteId`,`HorarioCursoId`,`FechaClase`,
   `Comentario`,`Estado`,`ObservacionCoordinador`,`Sesion`)
VALUES
  (1,2,7,NULL,'2026-06-24','olvidé registrarlo','pendiente',NULL,1),
  (2,46,2,NULL,'2026-06-29',NULL,'pendiente',NULL,1);

SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = 1;
