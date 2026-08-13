# Instrucciones del Agente - Integración RENIPRESS (SUSALUD)

## Directrices para el uso de la herramienta `consultar_datos_renipress`

1. **Identificación de Intención**:
   - Si el usuario menciona un número de 8 dígitos relacionado con una clínica, hospital o IPRESS (o pide buscar una clínica/establecimiento de salud por su código IDIPRESS), ejecuta inmediatamente la herramienta `consultar_datos_renipress`.

2. **Manejo de Errores**:
   - Si la herramienta devuelve un error de servidor (ej. 500, 404) o un mensaje de error, informa amablemente al usuario:
     > "El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido."

3. **Extracción y Formato de Datos**:
   - Analiza la respuesta estructurada en JSON (`nombre`, `estado`, `direccion`, `categoria`) y responde al usuario de forma clara y directa especificando el Nombre de la Clínica/Hospital, Estado (Activo/Inactivo), Categoría y Dirección.
