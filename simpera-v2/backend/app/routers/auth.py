"""Login & profil pengguna (memakai akun e-surat yang sudah ada)."""

from __future__ import annotations

from typing import Any

from fastapi import APIRouter, Depends, HTTPException, status

from .. import security
from ..common import clean
from ..schemas import LoginRequest, TokenResponse

router = APIRouter(prefix="/auth", tags=["auth"])


@router.post("/login", response_model=TokenResponse)
def login(payload: LoginRequest) -> Any:
    user = security.authenticate(payload.username.strip(), payload.password)
    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Username atau password salah.",
        )
    decorated = security.decorate(user)
    if int(decorated.get("role_id") or 0) == 0:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Akun Anda belum memiliki hak akses pada aplikasi.",
        )
    token, expires_in = security.create_token(decorated)
    return {
        "access_token": token,
        "token_type": "bearer",
        "expires_in": expires_in,
        "user": clean(decorated),
    }


@router.get("/me")
def me(user: dict[str, Any] = Depends(security.current_user)) -> Any:
    return clean(user)
