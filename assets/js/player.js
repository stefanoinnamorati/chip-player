(function () {
  "use strict";

  var root = document.querySelector("[data-chip-player], [data-pepa-sound]");
  if (!root) {
    return;
  }

  var dataNode = root.querySelector(".chip-player-data, .pepa-sound-data");
  if (!dataNode) {
    return;
  }

  var data;
  try {
    data = JSON.parse(dataNode.textContent);
  } catch (err) {
    return;
  }

  var tracks = data.tracks || [];
  var songs = data.songs || [];
  var i18n = data.i18n || {};
  if (!tracks.length) {
    return;
  }

  var audio = root.querySelector("[data-sound-audio]");
  var playBtn = root.querySelector("[data-sound-play]");
  var prevBtn = root.querySelector("[data-sound-prev]");
  var nextBtn = root.querySelector("[data-sound-next]");
  var seek = root.querySelector("[data-sound-seek]");
  var currentEl = root.querySelector("[data-sound-current]");
  var durationEl = root.querySelector("[data-sound-duration]");
  var titleEl = root.querySelector("[data-sound-title]");
  var metaEl = root.querySelector("[data-sound-meta]");
  var songEl = root.querySelector("[data-sound-song]");
  var noteEl = root.querySelector("[data-sound-note]");
  var songWrap = root.querySelector("[data-sound-songs]");
  var langWrap = root.querySelector("[data-sound-langs]");
  var variantWrap = root.querySelector("[data-sound-variants]");
  var langKicker = root.querySelector(".chip-player-lang-kicker, .pepa-sound-lang-kicker");
  var variantKicker = root.querySelector(".chip-player-variant-kicker, .pepa-sound-variant-kicker");
  var dock = root.querySelector("[data-sound-dock]");
  var dockPlay = root.querySelector("[data-sound-dock-play]");
  var dockTitle = root.querySelector("[data-sound-dock-title]");
  var dockMeta = root.querySelector("[data-sound-dock-meta]");
  var dockCover = root.querySelector("[data-sound-dock-cover]");
  var card = root.querySelector(".chip-player-card, .pepa-sound-card");
  var cardVisible = true;

  var state = {
    song: (songs[0] && songs[0].id) || (tracks[0] && tracks[0].song) || "",
    lang: tracks[0] ? tracks[0].lang : "",
    trackId: tracks[0] ? tracks[0].id : "",
    started: false,
  };

  var lastBySong = {};

  function fmt(seconds) {
    if (!isFinite(seconds) || seconds < 0) {
      return "0:00";
    }
    var m = Math.floor(seconds / 60);
    var s = Math.floor(seconds % 60);
    return m + ":" + String(s).padStart(2, "0");
  }

  function trackById(id) {
    for (var i = 0; i < tracks.length; i++) {
      if (tracks[i].id === id) {
        return tracks[i];
      }
    }
    return tracks[0];
  }

  function currentTrack() {
    return trackById(state.trackId);
  }

  function inSong(list, song) {
    return list.filter(function (t) {
      return t.song === song;
    });
  }

  function langsFor(song) {
    var seen = {};
    var out = [];
    inSong(tracks, song).forEach(function (t) {
      if (!seen[t.lang]) {
        seen[t.lang] = true;
        out.push({ id: t.lang, label: t.langLabel });
      }
    });
    return out;
  }

  function variantsFor(song, lang) {
    return inSong(tracks, song).filter(function (t) {
      return t.lang === lang;
    });
  }

  function queue() {
    var sameLang = variantsFor(state.song, state.lang);
    return sameLang.length ? sameLang : inSong(tracks, state.song);
  }

  function chip(label, active, onClick) {
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "chip-player-chip pepa-chip pepa-sound-chip" + (active ? " is-active" : "");
    btn.setAttribute("aria-pressed", active ? "true" : "false");
    btn.textContent = label;
    btn.addEventListener("click", onClick);
    return btn;
  }

  function renderChips() {
    songWrap.innerHTML = "";
    songs.forEach(function (song) {
      songWrap.appendChild(
        chip(song.label, song.id === state.song, function () {
          chooseSong(song.id);
        })
      );
    });

    var langs = langsFor(state.song);
    var showLangs = langs.length > 1;
    langWrap.hidden = !showLangs;
    if (langKicker) {
      langKicker.hidden = !showLangs;
    }
    langWrap.innerHTML = "";
    if (showLangs) {
      langs.forEach(function (lang) {
        langWrap.appendChild(
          chip(lang.label, lang.id === state.lang, function () {
            chooseLang(lang.id);
          })
        );
      });
    }

    var variants = variantsFor(state.song, state.lang);
    var showVariants = variants.length > 1;
    variantWrap.hidden = !showVariants;
    if (variantKicker) {
      variantKicker.hidden = !showVariants;
    }
    variantWrap.innerHTML = "";
    if (showVariants) {
      variants.forEach(function (track) {
        variantWrap.appendChild(
          chip(track.variant, track.id === state.trackId, function () {
            loadTrack(track.id, keepPlaying());
          })
        );
      });
    }

    var songMeta = songs.find(function (s) {
      return s.id === state.song;
    });
    noteEl.textContent = songMeta && songMeta.note ? songMeta.note : "";
  }

  function paintNow() {
    var track = currentTrack();
    songEl.textContent = track.songLabel;
    titleEl.textContent = track.title;
    metaEl.textContent = track.langLabel + " · " + track.variant;
    dockTitle.textContent = track.songLabel;
    dockMeta.textContent = track.variant;
    if (data.cover) {
      dockCover.src = data.cover;
    }
    playBtn.classList.toggle("is-playing", !audio.paused);
    dockPlay.classList.toggle("is-playing", !audio.paused);
    playBtn.setAttribute("aria-label", audio.paused ? i18n.play || "Play" : i18n.pause || "Pause");
    dockPlay.setAttribute("aria-label", audio.paused ? i18n.play || "Play" : i18n.pause || "Pause");
    var list = queue();
    var index = 0;
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === track.id) {
        index = i;
        break;
      }
    }
    prevBtn.disabled = index <= 0;
    nextBtn.disabled = index >= list.length - 1;
    document.body.classList.toggle("chip-player-on", state.started);
    document.body.classList.toggle("pepa-sound-on", state.started);
    syncDock();
  }

  function keepPlaying() {
    return !audio.paused;
  }

  function syncDock() {
    if (!dock) {
      return;
    }
    dock.hidden = !state.started || cardVisible;
  }

  function loadTrack(id, shouldPlay) {
    var track = trackById(id);
    state.trackId = track.id;
    state.song = track.song;
    state.lang = track.lang;
    lastBySong[track.song] = track.id;
    if (audio.src !== track.src) {
      audio.src = track.src;
    }
    renderChips();
    paintNow();
    if (shouldPlay) {
      var playPromise = audio.play();
      if (playPromise && typeof playPromise.catch === "function") {
        playPromise.catch(function () {});
      }
    }
  }

  function chooseSong(songId) {
    if (lastBySong[songId]) {
      loadTrack(lastBySong[songId], keepPlaying());
      return;
    }
    var first = inSong(tracks, songId)[0];
    if (first) {
      loadTrack(first.id, keepPlaying());
    }
  }

  function chooseLang(langId) {
    var variants = variantsFor(state.song, langId);
    if (!variants.length) {
      return;
    }
    var keep = variants.find(function (t) {
      return t.id === state.trackId;
    });
    loadTrack((keep || variants[0]).id, keepPlaying());
  }

  function togglePlay() {
    state.started = true;
    if (audio.paused) {
      if (!audio.src) {
        loadTrack(state.trackId, true);
        return;
      }
      audio.play().catch(function () {});
    } else {
      audio.pause();
    }
    paintNow();
  }

  function skip(dir) {
    var list = queue();
    var index = 0;
    for (var i = 0; i < list.length; i++) {
      if (list[i].id === state.trackId) {
        index = i;
        break;
      }
    }
    var next = list[index + dir];
    if (!next) {
      return;
    }
    loadTrack(next.id, keepPlaying() || state.started);
  }

  playBtn.addEventListener("click", togglePlay);
  dockPlay.addEventListener("click", togglePlay);
  prevBtn.addEventListener("click", function () {
    skip(-1);
  });
  nextBtn.addEventListener("click", function () {
    skip(1);
  });

  audio.addEventListener("play", function () {
    state.started = true;
    paintNow();
  });
  audio.addEventListener("pause", paintNow);
  audio.addEventListener("loadedmetadata", function () {
    seek.max = audio.duration || 0;
    durationEl.textContent = fmt(audio.duration);
  });
  audio.addEventListener("timeupdate", function () {
    if (document.activeElement !== seek) {
      seek.value = audio.currentTime || 0;
    }
    currentEl.textContent = fmt(audio.currentTime);
  });
  audio.addEventListener("ended", function () {
    var list = queue();
    var index = list.findIndex(function (t) {
      return t.id === state.trackId;
    });
    if (index > -1 && list[index + 1]) {
      loadTrack(list[index + 1].id, true);
      return;
    }
    paintNow();
  });

  seek.addEventListener("input", function () {
    audio.currentTime = Number(seek.value);
  });

  if (card && "IntersectionObserver" in window) {
    var observer = new IntersectionObserver(
      function (entries) {
        cardVisible = !!(entries[0] && entries[0].isIntersecting);
        syncDock();
      },
      { threshold: 0.35 }
    );
    observer.observe(card);
  }

  var hash = (location.hash || "").replace("#", "");
  if (hash && songs.some(function (s) { return s.id === hash; })) {
    chooseSong(hash);
  } else if (hash && tracks.some(function (t) { return t.id === hash; })) {
    loadTrack(hash, false);
  } else {
    loadTrack(state.trackId, false);
  }
})();
