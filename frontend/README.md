## Issues & Improvements
---

- El EBM tiene únicamente un set de Media Queries que debe extenderse a los sufijos: _up_, _down_, _only_.
- Los `grid-lists` del EBM necesitan extenderse para funcionar completamente con los media queries con los mismos sufijos _up_, _down_, _only_.
- Los contenedores custom deben funcionar únicamente en `@media md-up`


## Ayu Consulting S.A.C.

Ayu Consulting Frontend Boilerplate [@MadeByGus](http://soygus.com)

#### Atajos del CLI:

Si estás usando OSX, descarga [iTerm](http://iterm2.com/) e instala [Oh My ZSH](https://github.com/robbyrussell/oh-my-zsh) para poder utilizar los siguientes _aliases_ y plugins:

	# ALIASES
	# Sass Script
	alias sw="sass --watch"
	alias swm="sass --watch main.scss:main.css"
	alias swebm="sass --watch ebm.scss:ebm.css"
	# CoffeeScript
	alias coffeew="coffee --watch --compile --output lib/ src/"
	# GIT Clone EBM Boileplate
	alias gcbp="git clone https://github.com/ayuinc/bp_fe_ayu.git"

	# PLUGINS
	# Sublime text y GIT tools
	plugins=(git sublime)

__Instalación:__

Navega al directorio raíz: `cd ~` y ejecuta: `ls -a`, da click `cmd + click` en `.zshrc` para abrirlo en el editor de texto. Copia y pega el código anterior en ese documento, guarda y reinicia tu iTerm.

#### Acceso rápido a utilidades de JS

- [jQuery](http://jquery.com/download/)
- [VelocityJS](http://julian.com/research/velocity/)
- [Bootstrap 3](http://getbootstrap.com)

#### Consideraciones

Optimiza tu editor de texto así: 

		{
			"caret_style": "phase",
			"font_size": 14,
			"highlight_line": true,
			"line_padding_bottom": 1,
			"line_padding_top": 1,
			"margin": 2,
			"tab_size": 2 // ESTE ES EL MÁS IMPORTANTE y RELEVANTE
		}

__Al nombrar tus clases y id's piensa:__

> Si alguien más tomara este proyecto, sabría a qué me refiero?

