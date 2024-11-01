document.addEventListener('DOMContentLoaded', function () {
  var body = document.getElementsByClassName('ssf-active');
  body[0].insertAdjacentHTML('afterbegin', '<div id="ssf-particles-js"></div>');
  
  particlesJS.load('ssf-particles-js', ssf_script.ssf_url + 'assets/json/particles.json', function(e) {
    pJSDom[0].pJS.particles.color.value = ssf_script.color;
    pJSDom[0].pJS.particles.shape.type = ssf_script.shape;
    pJSDom[0].pJS.particles.number.value = ssf_script.number;
    pJSDom[0].pJS.fn.particlesRefresh();
  });

});