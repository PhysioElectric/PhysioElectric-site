export function validate(schema) {
  return (req, res, next) => {
    const parsed = schema.safeParse(req.body);
    if (!parsed.success) {
      return res.status(400).json({
        error: "invalid_payload",
        requestId: req.id,
        details: parsed.error.issues.slice(0, 8).map((i) => ({
          path: i.path.join("."),
          message: i.message,
        })),
      });
    }
    req.data = parsed.data;
    next();
  };
}

export function asyncHandler(fn) {
  return (req, res, next) => {
    Promise.resolve(fn(req, res, next)).catch(next);
  };
}
