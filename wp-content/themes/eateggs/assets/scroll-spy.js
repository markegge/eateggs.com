/**
 * TOC scroll-spy — highlights the section currently in view.
 * Mirrors the behaviour from the original static post.html design.
 */
(function () {
  var links = Array.prototype.slice.call(document.querySelectorAll('.toc .tl'));
  var map = links.map(function (a) {
    var id = a.getAttribute('href').slice(1);
    return { link: a, el: document.getElementById(id) };
  }).filter(function (m) { return m.el; });
  if (!map.length || !('IntersectionObserver' in window)) return;
  var current = null;
  var obs = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) current = e.target;
    });
    map.forEach(function (m) { m.link.classList.toggle('cur', m.el === current); });
  }, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });
  map.forEach(function (m) { obs.observe(m.el); });
})();
