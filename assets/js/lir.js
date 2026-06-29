/**
 * Levinger IG Reviews — feed interactions.
 * Filtering (FLIP reflow), accessible doctor dropdown, immersive lightbox.
 * Vanilla JS, no dependencies. Scoped per .lir instance.
 */
(function () {
	'use strict';

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}

	ready(function () {
		Array.prototype.forEach.call(document.querySelectorAll('.lir'), initInstance);
	});

	function initInstance(root) {
		if (root.__lirInit) { return; }
		root.__lirInit = true;

		var dataEl = root.querySelector('.lir__data');
		var reviews = [];
		if (dataEl) { try { reviews = JSON.parse(dataEl.textContent) || []; } catch (e) { reviews = []; } }
		if (!reviews.length) { return; }

		var grid = root.querySelector('.lir__grid');
		var cards = toArray(root.querySelectorAll('.lir__card'));
		var emptyEl = root.querySelector('.lir__empty:not(.lir__empty--initial)');
		var byIndex = {};
		reviews.forEach(function (r, i) { byIndex[i] = r; });

		var state = { procedure: 'all', doctor: 'all' };

		entrance(root, grid);
		var procCtl = initProcedures(root, state, function () { if (docCtl) { docCtl.reset(); } applyFilter(true); });
		var docCtl = initDoctor(root, state, function () { if (procCtl) { procCtl.reset(); } applyFilter(true); });
		initInitialFilter(root, state);
		applyFilter(false);

		var lb = initLightbox(root, reviews);
		cards.forEach(function (card) {
			card.addEventListener('click', function () {
				lb.open(visibleCards(), card);
			});
		});

		function visibleCards() {
			return cards.filter(function (c) { return !c.classList.contains('is-out'); });
		}

		function matches(card) {
			var p = state.procedure, d = state.doctor;
			var cp = (card.getAttribute('data-lir-procedure') || '').split(/\s+/);
			var cd = card.getAttribute('data-lir-doctor') || '';
			var okP = p === 'all' || cp.indexOf(p) !== -1;
			var okD = d === 'all' || cd === d;
			return okP && okD;
		}

		function applyFilter(animate) {
			var first = null;
			if (animate) {
				first = new Map();
				cards.forEach(function (c) { if (!c.classList.contains('is-out')) { first.set(c, c.getBoundingClientRect()); } });
			}

			cards.forEach(function (card) {
				var show = matches(card);
				var wasOut = card.classList.contains('is-out');
				card.classList.toggle('is-out', !show);
				if (show && wasOut && animate) {
					card.classList.add('is-entering');
					onAnimEnd(card, function () { card.classList.remove('is-entering'); });
				}
			});

			if (animate && first) {
				grid.classList.add('is-flipping');
				cards.forEach(function (card) {
					if (card.classList.contains('is-out')) { return; }
					var f = first.get(card);
					if (!f) { return; }
					var l = card.getBoundingClientRect();
					var dx = f.left - l.left, dy = f.top - l.top;
					if (dx || dy) {
						card.style.transition = 'none';
						card.style.transform = 'translate(' + dx + 'px,' + dy + 'px)';
						requestAnimationFrame(function () {
							requestAnimationFrame(function () {
								card.style.transition = '';
								card.style.transform = '';
							});
						});
					}
				});
				window.setTimeout(function () { grid.classList.remove('is-flipping'); }, 480);
			}

			updateEmpty();
		}

		function updateEmpty() {
			if (!emptyEl) { return; }
			var any = cards.some(function (c) { return !c.classList.contains('is-out'); });
			emptyEl.hidden = any;
			if (grid) { grid.style.display = any ? '' : 'none'; }
		}
	}

	/* ---------- entrance ---------- */
	function entrance(root, grid) {
		var go = function () { root.classList.add('is-ready'); };
		if ('IntersectionObserver' in window && grid) {
			var io = new IntersectionObserver(function (entries) {
				entries.forEach(function (en) {
					if (en.isIntersecting) { go(); io.disconnect(); }
				});
			}, { threshold: 0.08 });
			io.observe(grid);
		} else { go(); }
	}

	/* ---------- procedure chips ---------- */
	function initProcedures(root, state, onChange) {
		var btns = toArray(root.querySelectorAll('.lir__proc'));
		function setActive(btn) {
			btns.forEach(function (b) {
				var on = b === btn;
				b.classList.toggle('is-active', on);
				b.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		}
		btns.forEach(function (btn) {
			btn.addEventListener('click', function () {
				state.procedure = btn.getAttribute('data-lir-procedure') || 'all';
				setActive(btn);
				onChange();
			});
		});
		return {
			reset: function () {
				state.procedure = 'all';
				var allBtn = root.querySelector('.lir__proc[data-lir-procedure="all"]');
				if (allBtn) { setActive(allBtn); }
			}
		};
	}

	/* ---------- doctor dropdown (accessible listbox) ---------- */
	function initDoctor(root, state, onChange) {
		var wrap = root.querySelector('[data-lir-dropdown]');
		if (!wrap) { return { reset: function () {} }; }
		var btn = wrap.querySelector('.lir__doctor-btn');
		var menu = wrap.querySelector('.lir__doctor-menu');
		var label = wrap.querySelector('[data-lir-doctor-label]');
		var opts = toArray(wrap.querySelectorAll('.lir__doctor-opt'));

		function setOpen(open) {
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
			menu.hidden = !open;
			if (open) {
				document.addEventListener('click', outside, true);
				var sel = wrap.querySelector('.lir__doctor-opt.is-selected') || opts[0];
				if (sel) { sel.tabIndex = 0; sel.focus(); }
			} else {
				document.removeEventListener('click', outside, true);
			}
		}
		function outside(e) { if (!wrap.contains(e.target)) { setOpen(false); } }

		function mark(opt) {
			opts.forEach(function (o) {
				var on = o === opt;
				o.classList.toggle('is-selected', on);
				o.setAttribute('aria-selected', on ? 'true' : 'false');
			});
			if (label && opt) { label.textContent = opt.textContent; }
		}

		function select(opt) {
			state.doctor = opt.getAttribute('data-lir-doctor') || 'all';
			mark(opt);
			setOpen(false);
			btn.focus();
			onChange();
		}

		btn.addEventListener('click', function () { setOpen(btn.getAttribute('aria-expanded') !== 'true'); });
		btn.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpen(true); }
		});

		opts.forEach(function (opt, i) {
			opt.addEventListener('click', function () { select(opt); });
			opt.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); select(opt); }
				else if (e.key === 'ArrowDown') { e.preventDefault(); focusOpt(i + 1); }
				else if (e.key === 'ArrowUp') { e.preventDefault(); focusOpt(i - 1); }
				else if (e.key === 'Escape') { e.preventDefault(); setOpen(false); btn.focus(); }
				else if (e.key === 'Home') { e.preventDefault(); focusOpt(0); }
				else if (e.key === 'End') { e.preventDefault(); focusOpt(opts.length - 1); }
			});
		});
		function focusOpt(i) {
			var n = (i + opts.length) % opts.length;
			opts.forEach(function (o) { o.tabIndex = -1; });
			opts[n].tabIndex = 0;
			opts[n].focus();
		}

		return {
			reset: function () {
				state.doctor = 'all';
				mark(wrap.querySelector('.lir__doctor-opt[data-lir-doctor="all"]'));
			}
		};
	}

	/* ---------- initial pre-filter from shortcode atts ---------- */
	function initInitialFilter(root, state) {
		var p = (root.getAttribute('data-lir-init-procedure') || '').trim();
		var d = (root.getAttribute('data-lir-init-doctor') || '').trim();
		if (p) {
			var pb = root.querySelector('.lir__proc[data-lir-procedure="' + cssEsc(p) + '"]');
			if (pb) {
				state.procedure = p;
				toArray(root.querySelectorAll('.lir__proc')).forEach(function (b) {
					var on = b === pb;
					b.classList.toggle('is-active', on);
					b.setAttribute('aria-pressed', on ? 'true' : 'false');
				});
			}
		}
		if (d) {
			var opt = root.querySelector('.lir__doctor-opt[data-lir-doctor="' + cssEsc(d) + '"]');
			if (opt) {
				state.doctor = d;
				var label = root.querySelector('[data-lir-doctor-label]');
				toArray(root.querySelectorAll('.lir__doctor-opt')).forEach(function (o) {
					var on = o === opt;
					o.classList.toggle('is-selected', on);
					o.setAttribute('aria-selected', on ? 'true' : 'false');
				});
				if (label) { label.textContent = opt.textContent; }
			}
		}
	}

	/* ---------- lightbox ---------- */
	function initLightbox(root, reviews) {
		var lb = root.querySelector('.lir__lb');
		if (!lb) { return { open: function () {} }; }

		var reel = lb.querySelector('.lir__reel');
		var video = lb.querySelector('.lir__video');
		var elPill = lb.querySelector('[data-lir-lb-proc]');
		var elAvatar = lb.querySelector('[data-lir-lb-avatar]');
		var elPatient = lb.querySelector('[data-lir-lb-patient]');
		var elDoc = lb.querySelector('[data-lir-lb-doc]');
		var elCapnote = lb.querySelector('[data-lir-lb-capnote]');
		var elCaption = lb.querySelector('[data-lir-lb-caption]');
		var elQuote = lb.querySelector('[data-lir-lb-quote]');
		var cta = lb.querySelector('[data-lir-lb-cta]');
		var ctaText = lb.querySelector('[data-lir-lb-cta-text]');
		var progress = lb.querySelector('[data-lir-lb-progress]');
		var btnClose = lb.querySelector('[data-lir-close]');
		var btnPrev = lb.querySelector('[data-lir-prev]');
		var btnNext = lb.querySelector('[data-lir-next]');
		var btnToggle = lb.querySelector('[data-lir-toggle]');
		var btnLike = lb.querySelector('[data-lir-like]');
		var btnSave = lb.querySelector('[data-lir-save]');
		var btnShare = lb.querySelector('[data-lir-share]');

		var ctaUrl = root.getAttribute('data-lir-cta-url') || '';
		var ctaLabel = root.getAttribute('data-lir-cta-text') || '';

		var list = [];
		var pos = 0;
		var lastFocus = null;

		function reviewFor(card) { return reviews[parseInt(card.getAttribute('data-lir-index'), 10)] || null; }

		function render(review) {
			if (!review) { return; }
			video.pause();
			video.removeAttribute('src');
			video.poster = review.poster || '';
			video.src = review.video || '';
			video.currentTime = 0;
			if (progress) { progress.style.width = '0%'; }
			reel.classList.remove('is-playing');

			elPill.textContent = (review.procedures && review.procedures[0]) ? review.procedures[0].name : '';
			elPill.hidden = !elPill.textContent;
			if (review.doctorAvatar) { elAvatar.style.backgroundImage = 'url("' + review.doctorAvatar + '")'; elAvatar.hidden = false; }
			else { elAvatar.style.backgroundImage = ''; elAvatar.hidden = true; }
			if (elPatient) { elPatient.textContent = review.name || ''; }
			elDoc.textContent = review.doctor || '';

			var transcript = (review.transcript || '').trim();
			var quote = (review.quote || '').trim();
			if (transcript) {
				elCapnote.hidden = false;
				elCaption.textContent = transcript;
				elQuote.textContent = (quote && quote !== transcript) ? quote : '';
			} else {
				elCapnote.hidden = true;
				elCaption.textContent = quote;
				elQuote.textContent = '';
			}

			var ctaHref = review.doctorUrl || ctaUrl;
			if (ctaHref) {
				cta.hidden = false;
				cta.href = ctaHref;
				if (ctaText) { ctaText.textContent = ctaLabel; }
			} else {
				cta.hidden = true;
			}

			if (btnLike) { btnLike.classList.remove('is-on'); btnLike.setAttribute('aria-pressed', 'false'); }
			if (btnSave) { btnSave.classList.remove('is-on'); btnSave.setAttribute('aria-pressed', 'false'); }

			var solo = list.length <= 1;
			if (btnPrev) { btnPrev.hidden = solo; }
			if (btnNext) { btnNext.hidden = solo; }

			tryPlay();
		}

		function tryPlay() {
			var p = video.play();
			if (p && p.catch) { p.catch(function () { reel.classList.remove('is-playing'); }); }
		}

		function go(delta) {
			if (!list.length) { return; }
			pos = (pos + delta + list.length) % list.length;
			render(reviewFor(list[pos]));
		}

		function open(visible, card) {
			list = visible && visible.length ? visible : [card];
			pos = Math.max(0, list.indexOf(card));
			lastFocus = document.activeElement;
			document.body.classList.add('lir-lock');
			lb.hidden = false;
			requestAnimationFrame(function () { lb.classList.add('is-open'); });
			document.addEventListener('keydown', onKey, true);
			render(reviewFor(list[pos]));
			window.setTimeout(function () { (btnClose || reel).focus(); }, 60);
		}

		function close() {
			lb.classList.remove('is-open');
			document.removeEventListener('keydown', onKey, true);
			video.pause();
			window.setTimeout(function () {
				lb.hidden = true;
				video.removeAttribute('src');
				video.load();
			}, 320);
			document.body.classList.remove('lir-lock');
			if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
		}

		function onKey(e) {
			if (e.key === 'Escape') { e.preventDefault(); close(); }
			else if (e.key === 'ArrowRight') { e.preventDefault(); go(-1); }
			else if (e.key === 'ArrowLeft') { e.preventDefault(); go(1); }
			else if (e.key === 'Tab') { trapFocus(e); }
		}

		function trapFocus(e) {
			var f = toArray(lb.querySelectorAll('button, a[href], video, [tabindex]:not([tabindex="-1"])'))
				.filter(function (el) { return el.offsetParent !== null && !el.hidden; });
			if (!f.length) { return; }
			var first = f[0], last = f[f.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		}

		function togglePlay() { if (video.paused) { tryPlay(); } else { video.pause(); } }

		// wiring
		toArray(lb.querySelectorAll('[data-lir-close]')).forEach(function (el) { el.addEventListener('click', close); });
		if (btnPrev) { btnPrev.addEventListener('click', function () { go(-1); }); }
		if (btnNext) { btnNext.addEventListener('click', function () { go(1); }); }
		if (btnToggle) { btnToggle.addEventListener('click', togglePlay); }
		video.addEventListener('click', togglePlay);
		video.addEventListener('play', function () { reel.classList.add('is-playing'); });
		video.addEventListener('pause', function () { reel.classList.remove('is-playing'); });
		video.addEventListener('ended', function () { reel.classList.remove('is-playing'); });
		video.addEventListener('timeupdate', function () {
			if (progress && video.duration) { progress.style.width = (video.currentTime / video.duration * 100) + '%'; }
		});

		[btnLike, btnSave].forEach(function (b) {
			if (!b) { return; }
			b.addEventListener('click', function () {
				var on = !b.classList.contains('is-on');
				b.classList.toggle('is-on', on);
				b.setAttribute('aria-pressed', on ? 'true' : 'false');
			});
		});
		if (btnShare) {
			btnShare.addEventListener('click', function () {
				var review = reviewFor(list[pos]) || {};
				var url = review.igUrl || window.location.href;
				if (navigator.share) { navigator.share({ title: review.name || document.title, url: url }).catch(function () {}); }
				else if (navigator.clipboard) { navigator.clipboard.writeText(url).then(function () { flash(btnShare); }, function () {}); }
			});
		}

		return { open: open };
	}

	/* ---------- helpers ---------- */
	function toArray(nl) { return Array.prototype.slice.call(nl); }
	function onAnimEnd(el, fn) {
		var h = function () { fn(); el.removeEventListener('animationend', h); };
		el.addEventListener('animationend', h);
		window.setTimeout(h, 700);
	}
	function flash(el) { el.classList.add('is-on'); window.setTimeout(function () { el.classList.remove('is-on'); }, 900); }
	function cssEsc(s) { return (window.CSS && CSS.escape) ? CSS.escape(s) : String(s).replace(/["\\]/g, '\\$&'); }
})();
