# AWS ECR Deployment Helper Script
# Ensure you are logged into AWS CLI (`aws configure`) before running this.

$AWS_REGION = "us-east-1"
$REPO_NAME = "lumen-observability"

# 1. Get AWS Account ID
Write-Host "Retrieving AWS Account ID..." -ForegroundColor Cyan
$AWS_ACCOUNT_ID = (aws sts get-caller-identity --query Account --output text)
if ($null -eq $AWS_ACCOUNT_ID -or $AWS_ACCOUNT_ID -eq "") {
    Write-Error "Failed to retrieve AWS Account ID. Make sure 'aws' CLI is installed and configured."
    exit 1
}

$ECR_REGISTRY = "$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com"
$IMAGE_URI = "$ECR_REGISTRY/$REPO_NAME:latest"

# 2. Authenticate Docker with Amazon ECR
Write-Host "Authenticating Docker with Amazon ECR..." -ForegroundColor Cyan
aws ecr get-login-password --region $AWS_REGION | docker login --username AWS --password-stdin $ECR_REGISTRY
if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to authenticate Docker with ECR. Make sure Docker Desktop is running."
    exit 1
}

# 3. Create ECR Repository if it doesn't exist
Write-Host "Ensuring ECR repository exists..." -ForegroundColor Cyan
aws ecr describe-repositories --repository-names $REPO_NAME --region $AWS_REGION 2>$null
if ($LASTEXITCODE -ne 0) {
    aws ecr create-repository --repository-name $REPO_NAME --region $AWS_REGION
    Write-Host "ECR repository '$REPO_NAME' created." -ForegroundColor Green
}

# 4. Build the Docker Image
Write-Host "Building Docker image..." -ForegroundColor Cyan
docker build -t $REPO_NAME .
if ($LASTEXITCODE -ne 0) {
    Write-Error "Docker build failed."
    exit 1
}

# 5. Tag and Push the Image
Write-Host "Tagging image..." -ForegroundColor Cyan
docker tag "$REPO_NAME:latest" $IMAGE_URI

Write-Host "Pushing image to Amazon ECR (this may take a few minutes)..." -ForegroundColor Cyan
docker push $IMAGE_URI
if ($LASTEXITCODE -ne 0) {
    Write-Error "Failed to push image to ECR."
    exit 1
}

Write-Host "`nSuccessfully pushed container to ECR!" -ForegroundColor Green
Write-Host "Your Image URI is: $IMAGE_URI" -ForegroundColor Yellow
Write-Host "Copy this URI and paste it into ECS Express Mode." -ForegroundColor Yellow
