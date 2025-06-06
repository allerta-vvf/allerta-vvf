# Step 1: Build Angular in production mode
FROM node:22 AS builder

WORKDIR /app

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install --force

# Copy the rest of the application code
COPY . .

# Build the Angular application in production mode
RUN npm run build --ignore-scripts -- --output-path=./dist/frontend

# Step 2: Serve the built application using Nginx
FROM nginx:latest

# Copy the built Angular application from the previous step
COPY --from=builder /app/dist/frontend /usr/share/nginx/html

# Expose port 80 for Nginx
EXPOSE 80
