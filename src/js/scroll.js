window.addEventListener('scroll', scrollFunction)
var topBtn = document.getElementById('topBtn')

function scrollFunction() {
  if (window.innerWidth > 414) {
    if (
      document.body.scrollTop > 20 ||
      document.documentElement.scrollTop > 20
    ) {
      topBtn.style.display = 'block'
    } else {
      topBtn.style.display = 'none'
    }
  } else {
    return false
  }
}

topBtn.addEventListener('click', topFunction)

function topFunction() {
  document.body.scrollTop = 0 // For Safari
  document.documentElement.scrollTop = 0 // For Chrome, Firefox, IE and Opera
}
