export function notFound(req, res) {
  if (req.path.startsWith("/api")) {
    return res.status(404).json({ error: "not_found", path: req.path, requestId: req.id });
  }
  res.status(404).send("Not found");
}

export function errorHandler(err, req, res, _next) {
  const originErr = err?.message === "Origin not allowed";
  if (originErr) {
    return res.status(403).json({ error: "forbidden_origin", requestId: req.id });
  }

  const status = Number(err.status || err.statusCode) || 500;
  const expose = status < 500 || err.expose;
  if (status >= 500) {
    console.error(JSON.stringify({
      level: "error",
      requestId: req.id,
      path: req.path,
      message: err.message,
    }));
  }

  res.status(status).json({
    error: expose ? err.code || "request_failed" : "internal_error",
    message: expose ? err.message : "Internal error",
    requestId: req.id,
  });
}
