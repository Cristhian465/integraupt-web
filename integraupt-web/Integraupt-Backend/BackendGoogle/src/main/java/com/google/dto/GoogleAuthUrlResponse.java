package com.google.dto;

public record GoogleAuthUrlResponse(boolean success, String url, String message) {
}