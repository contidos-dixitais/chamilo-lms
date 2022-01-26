{%  if tool.description %}
    <p class="lead">{{ tool.description|e|nl2br }}</p>
{%  endif %}

Para acceder a la prueba proctorizada deberá completar el registro en SMOWL a través del siguiente enlace: <a href="https://swl.smowltech.net/monitor/controllerReg.php?entity_Name={{ entity_name }}&swlLicenseKey={{ license_key }}&user_idUser={{ user_id }}&lang={{ lang }}&course_Container={{ session_id }}&Course_link={{ course_link }}"
 target="_blank">SMOWL register</a>
