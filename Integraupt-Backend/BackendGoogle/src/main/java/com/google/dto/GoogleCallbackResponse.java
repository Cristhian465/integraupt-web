package com.google.dto;

public record GoogleCallbackResponse(boolean success, String message, GoogleUserProfile profile) {
}