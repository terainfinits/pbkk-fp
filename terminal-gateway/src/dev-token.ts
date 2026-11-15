import "dotenv/config";
import { SignJWT } from "jose";

const secret = new TextEncoder().encode(
  process.env.JWT_SECRET,
);

const token = await new SignJWT({
  session_id: "development-session",
})
  .setProtectedHeader({ alg: "HS256" })
  .setSubject("development-user")
  .setIssuer(process.env.JWT_ISSUER ?? "laravel-ide")
  .setAudience(process.env.JWT_AUDIENCE ?? "terminal-gateway")
  .setIssuedAt()
  .setExpirationTime("5m")
  .sign(secret);

console.log(token);
